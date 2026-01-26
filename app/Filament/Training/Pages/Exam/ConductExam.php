<?php

namespace App\Filament\Training\Pages\Exam;

use App\Enums\ExamResultEnum;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use App\Repositories\Cts\ExamAssessmentRepository;
use App\Repositories\Cts\ExamResultRepository;
use App\Services\Training\ExamResubmissionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Attributes\Session;

class ConductExam extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.conduct-exam';

    protected static ?string $slug = 'exams/conduct/{examId}';

    public ?array $data = [];

    public ?array $examResultData = [];

    public int $examId;

    public ExamBooking $examBooking;

    public bool $hasUnsavedChanges = false;

    public bool $isSaving = false;

    public ?int $lastChangedAt = null;

    public int $autosaveIdleSeconds = 1;

    public int $autosaveMinInterval = 5;

    public ?int $lastAutosaveAt = null;

    // save additional comments in session to persist across form submissions
    // this is because we don't save additional comments in the CTS database until the exam is completed

    #[Session('additionalComments.{examId}')]
    public ?string $additionalComments = '';

    protected function getForms(): array
    {
        return [
            'form',
            'examResultForm',
        ];
    }

    public function mount(): void
    {
        $exitErrorMessage = 'You do not have permission to conduct this exam.';
        try {
            $this->examBooking = ExamBooking::findOrFail($this->examId);
        } catch (ModelNotFoundException) {
            abort(403, $exitErrorMessage);
        }

        $permissionSafeExam = Str::lower($this->examBooking->exam);
        if (! auth()->user()->can("training.exams.conduct.{$permissionSafeExam}")) {
            abort(403, $exitErrorMessage);
        }

        if ($this->examBooking->finished == ExamBooking::FINISHED_FLAG || $this->examBooking->taken == 0) {
            abort(403, $exitErrorMessage);
        }

        $existingExamCriteriaAssessmentById = ExamCriteriaAssessment::where('examid', $this->examId)->get()
            ->mapWithKeys(
                function ($item) {
                    return [
                        $item->criteria_id => [
                            'grade' => $item->result ?? 'N',
                            'comments' => $item->notes,
                        ],
                    ];
                }
            );

        $existingAssessmentData = ExamCriteria::byType($this->examBooking->exam)
            ->get()
            ->mapWithKeys(
                function ($item) use ($existingExamCriteriaAssessmentById) {
                    $existingAssessment = $existingExamCriteriaAssessmentById->get($item->id);

                    return [
                        $item->id => [
                            'grade' => $existingAssessment['grade'] ?? 'N',
                            'comments' => $existingAssessment['comments'] ?? '',
                        ],
                    ];
                }
            );

        $this->form->fill(['form' => $existingAssessmentData]);

        $this->examResultForm->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save')
                ->action(fn () => $this->save())
                ->label(fn () => $this->hasUnsavedChanges ? 'Save' : 'Saved')
                ->icon(fn () => $this->hasUnsavedChanges ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check'),
        ];
    }

    public function examDetailsInfoList(Infolist $infolist)
    {
        $examinerFormat = function ($examiner) {
            return $examiner ? "{$examiner->account->name} ({$examiner->account->id})" : 'N/A';
        };

        return $infolist
            ->record($this->examBooking)
            ->schema([
                Section::make('Exam Details')->schema([
                    TextEntry::make('Student')->getStateUsing(fn () => "{$this->examBooking->studentAccount()->name} ({$this->examBooking->studentAccount()->id})"),
                    TextEntry::make('Student Rating')->getStateUsing(fn () => $this->examBooking->studentQualification->name),
                    TextEntry::make('position_1')->label('Position'),
                    TextEntry::make('Exam Start')->getStateUsing(fn () => $this->examBooking->startDate),
                    TextEntry::make('Exam End')->getStateUsing(fn () => $this->examBooking->endDate),
                    TextEntry::make('Exam Accepted At')->getStateUsing(fn () => $this->examBooking->time_taken),
                ])
                    ->columns(3),
                Section::make('Examiner Details')->schema([
                    TextEntry::make('Primary Examiner')
                        ->getStateUsing($examinerFormat($this->examBooking->examiners->primaryExaminer)),
                    TextEntry::make('Secondary Examiner')
                        ->getStateUsing($examinerFormat($this->examBooking->examiners->secondaryExaminer)),
                    TextEntry::make('Trainee Examiner')
                        ->getStateUsing($examinerFormat($this->examBooking->examiners->traineeExaminer)),
                ])
                    ->columns(3),
            ]);
    }

    public function form(Form $form): Form
    {
        $criteria = ExamCriteria::byType($this->examBooking->exam)->get();

        $criteriaComponents = $criteria->map(
            function (ExamCriteria $criteria) {
                return Fieldset::make("form.{$criteria->id}")
                    ->label($criteria->criteria)
                    ->schema([
                        RichEditor::make("form.{$criteria->id}.comments")
                            ->label('Comments')
                            ->default('')
                            ->columnSpan(9)
                            ->disableToolbarButtons(['attachFiles', 'blockquote'])
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn () => $this->markDirty()),
                        Select::make("form.{$criteria->id}.grade")
                            ->label('Grade')
                            ->options(ExamCriteriaAssessment::gradeDropdownOptions())
                            ->default(ExamCriteriaAssessment::NOT_ASSESSED)
                            ->columnSpan(3)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->save(withNotification: false)),
                    ])->columns(12);
            }
        );

        return $form
            ->schema([
                ...$criteriaComponents,
            ])
            ->statePath('data');
    }

    public function examResultForm(Form $form): Form
    {
        $completionComponents = Fieldset::make('completion')
            ->label('Exam Result')
            ->schema([
                RichEditor::make('additional_comments')
                    ->label('Additional Comments')
                    ->disableToolbarButtons(['attachFiles', 'blockquote'])
                    ->columnSpan(9)
                    ->live(debounce: 1000)
                    // save additional comments in session to persist in session in case navigation occurs
                    ->afterStateHydrated(fn ($component) => $component->state($this->additionalComments))
                    ->afterStateUpdated(fn ($state, $livewire) => ($this->additionalComments = $state)),

                Select::make('exam_result')
                    ->label('Result')
                    ->options([
                        ExamResultEnum::Pass->value => ExamResultEnum::Pass->human(),
                        ExamResultEnum::Fail->value => ExamResultEnum::Fail->human(),
                        ExamResultEnum::Incomplete->value => ExamResultEnum::Incomplete->human(),
                    ])
                    ->live()
                    ->columnSpan(3)
                    ->required(),

                Actions::make([
                    Actions\Action::make('submit_report')->action(fn () => $this->completeExam())
                        ->extraAttributes(['class' => 'w-full'])
                        ->label('Submit Report')
                        ->icon('heroicon-o-check')
                        ->disabled(fn (Get $get) => $get('exam_result') == null)
                        ->requiresConfirmation()
                        ->color('primary'),
                ])->id('submit_report_action')->alignment(Alignment::End)->columnSpan(12),
            ])->columns(12);

        return $form
            ->schema([
                $completionComponents,
            ])->statePath('examResultData');
    }

    public function completeExam()
    {
        $examResultFormData = $this->examResultForm->getState();

        $this->save(withNotification: false);

        if (! $this->validateGradesBeforeSubmission($examResultFormData['exam_result'])) {
            return;
        }

        (new ExamResultRepository)->createPracticalResult(
            examBooking: $this->examBooking,
            result: $examResultFormData['exam_result'],
            additionalComments: $examResultFormData['additional_comments'] ?? ''
        );

        Notification::make()
            ->title('Exam report submitted')
            ->success()
            ->send();

        app(ExamResubmissionService::class)->handle(
            examBooking: $this->examBooking,
            result: $examResultFormData['exam_result'],
            userId: auth()->id(),
        );

        $this->redirect(Exams::getUrl());
    }

    public function validateGradesBeforeSubmission(string $result): bool
    {
        $formData = collect($this->form->getState())['form'];

        $hasNotAssessed = collect($formData)->contains(
            fn ($item) => $item['grade'] === ExamCriteriaAssessment::NOT_ASSESSED
        );

        if ($result == PracticalResult::PASSED && $hasNotAssessed) {
            Notification::make()
                ->title('Cannot submit exam report')
                ->body('You cannot submit a pass result if there are criteria that have not been assessed.')
                ->danger()
                ->send();

            return false;
        }

        $hasFailedGrades = collect($formData)->contains(
            fn ($item) => $item['grade'] === ExamCriteriaAssessment::FAIL
        );

        if ($result == PracticalResult::PASSED && $hasFailedGrades) {
            Notification::make()
                ->title('Cannot submit exam report')
                ->body('You cannot submit a pass result if there are criteria that have failed.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public function save($withNotification = true): void
    {
        $this->isSaving = true;

        $formData = collect($this->form->getState())['form'];

        $flattenedFormData = collect($formData)->map(
            fn ($item, $key) => [
                'criteria_id' => $key,
                'grade' => $item['grade'],
                'comments' => $item['comments'],
            ]
        )
            ->values()
            ->all();

        collect($flattenedFormData)->each(
            fn ($item) => (new ExamAssessmentRepository)->upsertExamCriteriaAssessment(
                examId: $this->examId,
                criteriaId: $item['criteria_id'],
                grade: $item['grade'],
                comments: $item['comments'] ?? null,
            )
        );

        $this->hasUnsavedChanges = false;
        $this->isSaving = false;

        if ($withNotification) {
            Notification::make()
                ->title('Exam report saved')
                ->success()
                ->send();
        }
    }

    public function autosave(): void
    {
        if ($this->isSaving || ! $this->hasUnsavedChanges) {
            return;
        }

        $now = now()->timestamp;

        if ($this->lastChangedAt && ($now - $this->lastChangedAt) < $this->autosaveIdleSeconds) {
            return;
        }

        if ($this->lastAutosaveAt && ($now - $this->lastAutosaveAt) < $this->autosaveMinInterval) {
            return;
        }

        $this->lastAutosaveAt = $now;

        $this->save(withNotification: false);
    }

    public function markDirty(): void
    {
        $this->hasUnsavedChanges = true;
        $this->lastChangedAt = now()->timestamp;
    }
}
