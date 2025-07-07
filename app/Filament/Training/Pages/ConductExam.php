<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use Faker\Provider\Text;
use Filament\Actions\Action;
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

class ConductExam extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.conduct-exam';

    protected static ?string $slug = 'exams/conduct/{id?}';

    public ?array $data = [];

    public ?int $examId = null;

    private ExamBooking $examBooking;

    public function mount(): void
    {
        $this->examId = request()->route('id');

        $this->examBooking = ExamBooking::findOrFail($this->examId);

        $existingAssessmentData = ExamCriteriaAssessment::where('examid', $this->examId)
            ->get()
            ->mapWithKeys(
                function ($item) {
                    return [
                        $item->criteria_id => [
                            'grade' => $item->result,
                            'comments' => $item->notes,
                        ],
                    ];
                }
            )
            ->toArray();

        $this->form->fill(['form' => $existingAssessmentData]);
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
                        ->getStateUsing(function ()  {
                            $examiner = $this->examBooking->examiners->primaryExaminer;
                            return "{$examiner->account->name} ({$examiner->account->id})";
                        }),
                    TextEntry::make('Secondary Examiner')
                        ->getStateUsing(function ()  {
                            $examiner = $this->examBooking->examiners->secondaryExaminer;
                            return $examiner ? "{$examiner->account->name} ({$examiner->account->id})" : 'N/A';
                        }),
                ])
                ->columns(2),
            ]);
    }

    public function form(Form $form): Form
    {
        $criteria = ExamCriteria::byType('TWR')->get();

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

    public function save(): void
    {
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
                        'notes' => $item['comments'],
                        'addnotes' => $item['comments'] ? true : false,
                    ],
                );
            }
        );

        Notification::make()
            ->title('Exam report saved')
            ->success()
            ->send();
    }
}
