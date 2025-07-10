<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use Faker\Provider\Text;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
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

    #[Session('additionalComments.{examId}')]
    public ?string $additionalComments = '';

    protected function getForms(): array
    {
        return [
            'form',
            'examResultForm'
        ];
    }

    public function mount(): void
    {
        $this->examBooking = ExamBooking::findOrFail($this->examId);

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
            Action::make('Save')->action(fn () => $this->save())
                ->label('Save')
                ->icon('heroicon-o-check'),
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
                    TextEntry::make('aditional_comments')->getStateUsing(fn () => $this->additionalComments)
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
                            ->live(debounce: 1000)
                            ->afterStateUpdated(fn () => $this->save()),
                        Select::make("form.{$criteria->id}.grade")
                            ->label('Grade')
                            ->options([
                                'P' => 'Fully Competent',
                                'M' => 'Mostly Competent',
                                'R' => 'Partially Competent',
                                'N' => 'Not Assessed',
                                'F' => 'Fail',
                            ])
                            ->default('N')
                            ->columnSpan(3)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->save()),
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
        $additionalCommentsComponents = Fieldset::make('form.additional_comments')
            ->label('Exam Result')
            ->schema([
                RichEditor::make('additional_comments')
                    ->label('Additional Comments')
                    ->disableToolbarButtons(['attachFiles', 'blockquote'])
                    ->columnSpan(9)
                    ->live(debounce: 1000)
                    ->afterStateHydrated(fn ($component) => $component->state($this->additionalComments))
                    ->afterStateUpdated(fn ($state, $livewire) => ($this->additionalComments = $state)),

                Select::make('exam_result')
                    ->label('Result')
                    ->options([
                        'P' => 'Pass',
                        'F' => 'Fail',
                        'N' => 'Incomplete',
                    ])
                    ->columnSpan(3)
                    ->required(),

                Actions::make([
                    Actions\Action::make("Submit Report")->action(fn() => $this->completeExam())->extraAttributes(['class' => 'w-full'])
                        ->label('Submit Report')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->color('primary'),
                ])->alignment(Alignment::End)->columnSpan(12)
            ])->columns(12);

        return $form
            ->schema([
                $additionalCommentsComponents,
            ])->statePath('examResultData');
    }

    public function completeExam()
    {
        $examResultFormData = $this->examResultForm->getState();

        $this->save(withNotification: false);

        PracticalResult::create([
            'examid' => $this->examId,
            'student_id' => $this->examBooking->student_id,
            'result' => $examResultFormData['exam_result'],
            'notes' => $examResultFormData['additional_comments'] ?? '',
            'date' => now(),
            'exam' => $this->examBooking->exam,
        ]);
    }

    public function save($withNotification = true): void
    {
        $formData = collect($this->form->getState())['form'];

        $flattenedFormData = collect($formData)->except(['additional_comments', 'exam_result'])->map(
            fn ($item, $key) => [
                'criteria_id' => $key,
                'grade' => $item['grade'],
                'comments' => $item['comments'],
            ]
        )
            ->values()
            ->all();

        collect($flattenedFormData)->each(
            function ($item) {
                ExamCriteriaAssessment::updateOrCreate(
                    [
                        'examid' => $this->examId,
                        'criteria_id' => $item['criteria_id'],
                    ],
                    [
                        'examid' => $this->examId,
                        'criteria_id' => $item['criteria_id'],
                        'result' => $item['grade'],
                        'notes' => $item['comments'] ?? "",
                        'addnotes' => $item['comments'] ? true : false,
                    ],
                );
            }
        );

        if ($withNotification) {
            Notification::make()
                ->title('Exam report saved')
                ->success()
                ->send();
        }
    }
}
