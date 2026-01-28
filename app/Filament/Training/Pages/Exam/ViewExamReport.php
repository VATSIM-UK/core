<?php

namespace App\Filament\Training\Pages\Exam;

use App\Enums\ExamResultEnum;
use App\Infolists\Components\PracticalExamCriteriaResult;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use App\Services\Training\ExamResubmissionService;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Fieldset;
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
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Get;

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
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->visible(fn () => auth()->user()->can('training.exams.override-result'))
                        ->form([
                            \Filament\Forms\Components\Section::make('Exam Result')->columns(2)->schema([
                                Select::make('previous_exam_result')
                                    ->label('Previous Result')
                                    ->default($this->practicalResult->result)
                                    ->options([
                                        ExamResultEnum::Pass->value => ExamResultEnum::Pass->human(),
                                        ExamResultEnum::Fail->value => ExamResultEnum::Fail->human(),
                                        ExamResultEnum::Incomplete->value => ExamResultEnum::Incomplete->human(),
                                    ])
                                    ->required()
                                    ->disabled()
                                    ->columns(1)
                                    ->dehydrated(true),
                                Select::make('exam_result')
                                    ->label('New Result')
                                    ->default($this->practicalResult->result)
                                    ->live()
                                    ->columns(1)
                                    ->options([
                                        ExamResultEnum::Pass->value => ExamResultEnum::Pass->human(),
                                        ExamResultEnum::Fail->value => ExamResultEnum::Fail->human(),
                                        ExamResultEnum::Incomplete->value => ExamResultEnum::Incomplete->human(),
                                    ])
                                    ->required(),
                                Textarea::make('reason')
                                    ->label('Reason for exam result change')
                                    ->placeholder('Explain why this exam result was adjusted')
                                    ->required()
                                    ->columnSpanFull(),
                                    ]),

                            \Filament\Forms\Components\Section::make('Exam Criteria')
                                ->visible(fn ($get) =>
                                    $get('exam_result') !== $this->practicalResult->result
                                )
                                ->schema(function () {
                                    $criteria = ExamCriteria::byType($this->practicalResult->examBooking->exam)->get();
                                    $existingAssessments = $this->practicalResult->criteria->pluck('result', 'criteria_id');

                                    return $criteria->map(function (ExamCriteria $criteria) use ($existingAssessments) {
                                        return Fieldset::make("criteria_updates.{$criteria->id}")
                                            ->label($criteria->criteria)
                                            ->schema([
                                                Select::make("criteria_updates.previous_{$criteria->id}.grade")
                                                    ->label('Previous Grade')
                                                    ->options(ExamCriteriaAssessment::gradeDropdownOptions())
                                                    ->default($existingAssessments->get($criteria->id))
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                Select::make("criteria_updates.{$criteria->id}.grade")
                                                    ->label('New Grade')
                                                    ->options(ExamCriteriaAssessment::gradeDropdownOptions())
                                                    ->default($existingAssessments->get($criteria->id))
                                                    ->live()
                                                    ->required(),
                                                    
                                                Textarea::make("criteria_updates.{$criteria->id}.change_comments")
                                                    ->label('Reason for criteria change')
                                                    ->placeholder('Explain why this specific criteria grade was adjusted')
                                                    ->required(fn (Get $get) => 
                                                        $get("criteria_updates.{$criteria->id}.grade") !== ($existingAssessments->get($criteria->id))
                                                    )
                                                    ->visible(fn (Get $get) => 
                                                        $get("criteria_updates.{$criteria->id}.grade") !== ($existingAssessments->get($criteria->id))
                                                    )
                                                    ->columnSpanFull(),
                                            ]);
                                    })->toArray();}
                                ),
                            ])
                        ->action(fn (array $data) => $this->overrideResult($data)),
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
        $criteriaUpdates = $data['criteria_updates'] ?? [];

        $this->practicalResult->update(['result' => $newResult->value]);

        $account = $this->practicalResult->examBooking->student->account;

        $account->addNote(noteType: 'training',
            noteContent: "Exam result for {$this->practicalResult->examBooking->exam} overridden to {$newResult->human()}. Reason: {$data['reason']}",
            writer: auth()->user(),
        );

        foreach ($criteriaUpdates as $criteriaId => $update) {
            $assessment = ExamCriteriaAssessment::where('examid', $this->practicalResult->examid)
                ->where('criteria_id', $criteriaId)
                ->first();

            $oldGrade = $assessment->result;
            $newGrade = $update['grade'] ?? $oldGrade;

            if ($oldGrade !== $newGrade) {
                $assessment->result = $newGrade;
                $assessment->save();

                $criteriaName = ExamCriteria::find($criteriaId)?->criteria ?? "Criteria #{$criteriaId}";
                
                $account->addNote(
                    noteType: 'training',
                    noteContent: "'{$criteriaName}' updated from {$oldGrade} to {$newGrade}. Reason: " . ($update['change_comments'] ?? 'No comment.'),
                    writer: auth()->user(),
                );
            }
        }

        app(ExamResubmissionService::class)->handle(
            examBooking: $this->practicalResult->examBooking,
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
