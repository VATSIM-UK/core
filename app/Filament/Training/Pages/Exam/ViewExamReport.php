<?php

namespace App\Filament\Training\Pages\Exam;

use App\Enums\ExamResultEnum;
use App\Infolists\Components\PracticalExamCriteriaResult;
use App\Models\Cts\PracticalResult;
use App\Services\Training\ExamResubmissionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ViewExamReport extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.view-exam-report';

    protected static ?string $slug = 'exams/report/{examId}';

    public int $examId;

    public PracticalResult $practicalResult;

    public function mount(): void
    {
        // Check basic training exams access
        if (! auth()->user()->can('training.exams.access')) {
            abort(403, 'You do not have permission to access training exams.');
        }

        $this->practicalResult = PracticalResult::where('examid', $this->examId)->firstOrFail();

        // Check specific conduct permission for this exam level
        if ($this->practicalResult->examBooking) {
            $examLevel = strtolower($this->practicalResult->examBooking->exam);
            if (! auth()->user()->can("training.exams.conduct.{$examLevel}")) {
                abort(403, 'You do not have permission to view this exam report.');
            }
        } else {
            abort(403, 'Invalid exam booking.');
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->record($this->practicalResult)->schema([
            Section::make('')->schema([
                Section::make('Student')->schema([
                    TextEntry::make('student.account.name')->label('Name'),
                    TextEntry::make('student.account.id')->label('CID'),
                    TextEntry::make('examBooking.studentQualification.name')->label('Qualification'),
                ])->columns(2)->columnSpan(1)->extraAttributes(['class' => 'h-full']),

                Section::make('Exam')->schema([
                    TextEntry::make('examBooking.exam')->label('Exam'),
                    TextEntry::make('examBooking.position_1')->label('Position'),
                    TextEntry::make('examBooking.start_date')->label('Date'),
                    TextEntry::make('examBooking.examiners.primaryExaminer.account.name')->label('Primary Examiner'),
                    TextEntry::make('examBooking.examiners.secondaryExaminer.account.name')->label('Secondary Examiner'),
                    TextEntry::make('examBooking.examiners.traineeExaminer.account.name')->label('Trainee Examiner'),
                ])->columns(2)->columnSpan(1)->extraAttributes(['class' => 'h-full']),
            ])->columns(2)->extraAttributes(['class' => 'items-stretch']),

            Section::make('Exam Result')
                ->headerActions([
                    Action::make('override_result')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->visible(fn () => auth()->user()->can('training.exams.override-result'))
                        ->form([
                            Select::make('exam_result')
                                ->label('Result')
                                ->options([
                                    ExamResultEnum::Pass->value => ExamResultEnum::Pass->human(),
                                    ExamResultEnum::Fail->value => ExamResultEnum::Fail->human(),
                                    ExamResultEnum::Incomplete->value => ExamResultEnum::Incomplete->human(),
                                ])
                                ->required(),
                            Textarea::make('reason')
                                ->label('Reason')
                                ->required(),
                        ])
                        ->action(fn (array $data) => $this->overrideResult($data))
                        ->requiresConfirmation(),
                ])
                ->schema([
                    TextEntry::make('result')->label('Result')->badge()->color(fn ($state) => match ($state) {
                        'Passed' => 'success',
                        'Failed' => 'danger',
                        'Incomplete' => 'warning',
                        default => 'gray',
                    })->getStateUsing(fn ($record) => $record->resultHuman()),

                    TextEntry::make('notes')->html()->extraAttributes(['style' => 'word-break:break-word'])->label('Additional Comments'),

            ])->columns(2)->extraAttributes(['class' => 'items-stretch']),
        ]);
    }

    public function criteriaInfoList(Infolist $infolist): Infolist
    {
        return $infolist->record($this->practicalResult)->schema([
            RepeatableEntry::make('criteria')->label('')->schema([
                TextEntry::make('examCriteria.criteria')->label(null)->columnSpan(10),
                PracticalExamCriteriaResult::make('result')->label('Result')->columnSpan(2),
                TextEntry::make('notes')->extraAttributes(['style' => 'word-break:break-word'])->html()->label('Notes')->columnSpan(12),
            ])->columns(12),
        ]);
    }

    public function overrideResult($data)
    {
        $newResult = ExamResultEnum::from($data['exam_result']);

        $this->practicalResult->update(['result' => $newResult->value]);

        app(ExamResubmissionService::class)->handle(
            examBooking: $this->examBooking,
            result: $newResult->value,
            userId: auth()->id(),
        );

        Notification::make()
            ->title('Exam result amended')
            ->body("Exam result updated to {$newResult->human()}")
            ->success()
            ->send();
    }
}
