<?php

namespace App\Filament\Training\Pages\Exam;

use App\Enums\ExamResultEnum;
use App\Filament\Forms\Components\TrainingRichEditor;
use App\Filament\Training\Concerns\InteractsWithCtsRichEditorNotes;
use App\Infolists\Components\PracticalExamCriteriaResult;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use App\Services\Training\OverrideExamReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ViewExamReport extends Page implements HasInfolists
{
    use InteractsWithCtsRichEditorNotes;
    use InteractsWithInfolists;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.training.pages.view-exam-report';

    protected static ?string $slug = 'exams/report/{examId}';

    public int $examId;

    public PracticalResult $practicalResult;

    public function mount(): void
    {
        $this->practicalResult = PracticalResult::where('examid', $this->examId)
            ->with('examBooking')
            ->firstOrFail();

        $user = auth()->user();

        // Students may always view their own report
        if ($this->practicalResult->student?->cid === $user->id) {
            return;
        }

        // Examiner path
        if (! $user->can('training.exams.access')) {
            abort(403, 'You do not have permission to access training exams.');
        }

        if (! $this->practicalResult->examBooking) {
            abort(404, 'Invalid exam booking.');
        }

        $examLevel = strtolower($this->practicalResult->examBooking->exam);

        if (! $user->can("training.exams.conduct.{$examLevel}")) {
            abort(403, 'You do not have permission to view this exam report.');
        }
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->record($this->practicalResult)->components([
            Section::make('')->columnSpanFull()->schema([
                Section::make('Student')->columnSpanFull()->schema([
                    TextEntry::make('student.account.name')->label('Name'),
                    TextEntry::make('student.account.id')->label('CID'),
                    TextEntry::make('examBooking.studentQualification.name')->label('Qualification'),
                ])->columns(2)->columnSpan(1)->extraAttributes(['class' => 'h-full']),

                Section::make('Exam')->columnSpanFull()->schema([
                    TextEntry::make('examBooking.exam')->label('Exam'),
                    TextEntry::make('examBooking.position_1')->label('Position'),
                    TextEntry::make('examBooking.start_date')->label('Date'),
                    TextEntry::make('examBooking.examiners.primaryExaminer.account.name')->label('Primary Examiner'),
                    TextEntry::make('examBooking.examiners.secondaryExaminer.account.name')->label('Secondary Examiner'),
                    TextEntry::make('examBooking.examiners.traineeExaminer.account.name')->label('Trainee Examiner'),
                ])->columns(2)->columnSpan(1)->extraAttributes(['class' => 'h-full']),
            ])->columns(2)->extraAttributes(['class' => 'items-stretch']),

            Section::make('Exam Result')
                ->columnSpanFull()
                ->headerActions([
                    Action::make('override_result')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->modalHeading('Override Exam Result')
                        ->modalDescription('Update the outcome, criteria grades, and comments..')
                        ->modalWidth(Width::SevenExtraLarge)
                        ->modalSubmitActionLabel('Override Exam Result')
                        ->visible(fn () => auth()->user()->can('training.exams.override-result'))
                        ->schema([
                            Section::make('Outcome')->columns(12)->columnSpanFull()->schema([
                                Select::make('previous_exam_result')
                                    ->label('Previous Result')
                                    ->default($this->practicalResult->result)
                                    ->options(fn () => $this->practicalResult->examBooking->isPilotExam() ? ExamResultEnum::pilotOptions() : ExamResultEnum::atcOptions())
                                    ->required()
                                    ->disabled()
                                    ->columnSpan(6)
                                    ->dehydrated(true),

                                Select::make('exam_result')
                                    ->label('New Result')
                                    ->default($this->practicalResult->result)
                                    ->live()
                                    ->options(fn () => $this->practicalResult->examBooking->isPilotExam() ? ExamResultEnum::pilotOptions() : ExamResultEnum::atcOptions())
                                    ->required()
                                    ->columnSpan(6),

                                TrainingRichEditor::make('additional_comments')
                                    ->label('Additional Comments')
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(fn ($component) => $component->state(
                                        $this->ctsRichEditorHtmlForHydration($this->practicalResult->notes)
                                    ))
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 180px;',
                                    ])
                                    ->columnSpanFull(),

                                Textarea::make('reason')
                                    ->label('Reason for override')
                                    ->placeholder('Explain why the exam result, criteria, or comments were adjusted')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                            Section::make('Criteria Updates')
                                ->columnSpanFull()
                                ->description('Change each criterias grade and comments individually.')
                                ->visible(fn (Get $get) => ! $this->practicalResult->examBooking->isPilotExam()
                                    && $get('exam_result') !== $this->practicalResult->result
                                )
                                ->schema(function () {
                                    $criteria = ExamCriteria::byType($this->practicalResult->examBooking->exam)->get();
                                    $existingAssessments = $this->practicalResult->criteria->keyBy('criteria_id');

                                    return $criteria->map(function (ExamCriteria $criteria) use ($existingAssessments) {
                                        $assessment = $existingAssessments->get($criteria->id);
                                        $existingGrade = $assessment?->result ?? ExamCriteriaAssessment::NOT_ASSESSED;
                                        $existingNotesForCompare = $this->ctsRichContentNotesForCts($assessment?->notes) ?? '';

                                        return Fieldset::make("criteria_updates.{$criteria->id}")
                                            ->label($criteria->criteria)
                                            ->columnSpanFull()
                                            ->columns(12)
                                            ->schema([
                                                Select::make("criteria_updates.previous_{$criteria->id}.grade")
                                                    ->label('Previous Grade')
                                                    ->options(ExamCriteriaAssessment::gradeDropdownOptions())
                                                    ->default($existingGrade)
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->columnSpan(4),

                                                Select::make("criteria_updates.{$criteria->id}.grade")
                                                    ->label('New Grade')
                                                    ->options(ExamCriteriaAssessment::gradeDropdownOptions())
                                                    ->default($existingGrade)
                                                    ->live()
                                                    ->required()
                                                    ->columnSpan(4),

                                                TrainingRichEditor::make("criteria_updates.{$criteria->id}.notes")
                                                    ->label('Report comments for this criteria')
                                                    ->live(onBlur: true)
                                                    ->afterStateHydrated(fn ($component) => $component->state(
                                                        $this->ctsRichEditorHtmlForHydration($assessment?->notes)
                                                    ))
                                                    ->extraInputAttributes([
                                                        'style' => 'min-height: 140px;',
                                                    ])
                                                    ->columnSpanFull(),

                                                Textarea::make("criteria_updates.{$criteria->id}.change_comments")
                                                    ->label('Reason for criteria change')
                                                    ->placeholder('Explain why this specific criteria grade was adjusted')
                                                    ->required(function (Get $get) use ($existingGrade, $existingNotesForCompare, $criteria): bool {
                                                        $gradeChanged = $get("criteria_updates.{$criteria->id}.grade") !== $existingGrade;
                                                        $notesNow = $this->ctsRichContentNotesForCts($get("criteria_updates.{$criteria->id}.notes") ?? null) ?? '';

                                                        return $gradeChanged || $notesNow !== $existingNotesForCompare;
                                                    })
                                                    ->visible(function (Get $get) use ($existingGrade, $existingNotesForCompare, $criteria): bool {
                                                        $gradeChanged = $get("criteria_updates.{$criteria->id}.grade") !== $existingGrade;
                                                        $notesNow = $this->ctsRichContentNotesForCts($get("criteria_updates.{$criteria->id}.notes") ?? null) ?? '';

                                                        return $gradeChanged || $notesNow !== $existingNotesForCompare;
                                                    })
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ]);
                                    })->toArray();
                                }
                                ),
                        ])
                        ->action(fn (array $data) => $this->overrideResult($data)),
                ])
                ->schema([
                    TextEntry::make('result')->label('Result')->badge()->color(fn ($state) => match ($state) {
                        'Passed' => 'success',
                        'Partial Pass' => 'warning',
                        'Failed' => 'danger',
                        'Incomplete' => 'warning',
                        default => 'gray',
                    })->getStateUsing(fn ($record) => $record->resultHuman()),

                    TextEntry::make('notes')->html()->extraAttributes(['style' => 'word-break:break-word'])->label('Additional Comments'),

                ])->columns(2)->extraAttributes(['class' => 'items-stretch']),
        ]);
    }

    public function criteriaInfoList(Schema $schema): Schema
    {
        return $schema->record($this->practicalResult)->components([
            RepeatableEntry::make('criteria')->label('')->schema([
                TextEntry::make('examCriteria.criteria')->label(null)->columnSpan(10),
                PracticalExamCriteriaResult::make('result')->label('Result')->columnSpan(2),
                TextEntry::make('notes')->extraAttributes(['style' => 'word-break:break-word'])->html()->label('Notes')->columnSpan(12),
            ])->columns(12),
        ]);
    }

    public function overrideResult(array $data): void
    {
        $hasChanges = app(OverrideExamReportService::class)->handle($this->practicalResult, $data, auth()->user());

        if (! $hasChanges) {
            Notification::make()
                ->title('No changes to save')
                ->body('Update the result, comments, or criteria before saving the override.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Exam override saved')
            ->body('The exam report and candidate notes have been updated.')
            ->success()
            ->send();
    }
}
