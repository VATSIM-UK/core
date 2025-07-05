<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ConductExam extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.conduct-exam';

    protected static ?string $slug = 'exams/conduct/{id?}';

    public ?array $data = [];

    public ?int $examId = null;

    public function mount(): void
    {
        $this->examId = request()->route('id');

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
                            ->required()
                            ->columnSpan(9)
                            ->disableToolbarButtons(['attachFiles', 'blockquote']),
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
                            ->required(),
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
                fn($item, $key) => [
                    "criteria_id" => $key,
                    "grade" => $item["grade"],
                    "comments" => $item["comments"]
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
