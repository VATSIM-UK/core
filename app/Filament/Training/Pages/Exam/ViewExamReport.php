<?php

namespace App\Filament\Training\Pages\Exam;

use App\Infolists\Components\PracticalExamCriteriaResult;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action as PageAction;
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

    public function getActions(): array
    {
        return [
            PageAction::make('updateExam')
                ->label('Update Exam')
                ->icon('heroicon-o-pencil')
                ->visible(fn () => auth()->user()->can('training.exams.override'))
                ->form([
                    FormSection::make('Overall Result')->schema([
                        Select::make('result')
                            ->label('Result')
                            ->options([
                                PracticalResult::PASSED => 'Passed',
                                PracticalResult::FAILED => 'Failed',
                                PracticalResult::INCOMPLETE => 'Incomplete',
                            ])
                            ->default(fn () => $this->practicalResult->result)
                            ->required(),
                        RichEditor::make('notes')
                            ->label('Additional Comments')
                            ->disableToolbarButtons(['attachFiles', 'blockquote'])
                            ->columnSpan(9)
                            ->required()
                            ->default(fn () => $this->practicalResult->notes),
                        TextArea::make('result_update_reason')
                            ->label('Internal Note')
                            ->helperText('Adds an internal note only visible to users who can edit exam results explaining why the result was changed.')
                            ->rows(3)
                            ->required(),
                    ]),

                    FormSection::make('Criteria')->schema([
                        Repeater::make('criteria')->label('')->schema([
                            Placeholder::make('criteria_text')->label('Criteria')->content(fn ($get) => $get('criteria_text')),

                            Select::make('result')->label('Result')->options(ExamCriteriaAssessment::gradeDropdownOptions())->required(),

                            RichEditor::make('notes')->label('Notes')
                                ->disableToolbarButtons(['attachFiles', 'blockquote'])
                                ->required(),

                        ])->addable(false)->deletable(false)->reorderable(false)
                            ->default(fn () => $this->practicalResult->criteria->map(function ($assessment) {
                                return [
                                    'id' => $assessment->id,
                                    'criteria_text' => $assessment->examCriteria->criteria,
                                    'result' => $assessment->result,
                                    'notes' => $assessment->notes,
                                ];
                            })->toArray()),
                    ]),
                ])
                ->modalHeading('Update Exam')
                ->modalSubHeading('Update the result and comments for this exam report.')
                ->action(function (array $data) {
                    $resultData = [
                        'result_update_reason' => $data['result_update_reason'],
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ];

                    // Only record previous result if it has changed. Always require and record an exam updated reason.
                    if ($this->practicalResult->result !== $data['result']) {
                        $resultData['previous_result'] = $this->practicalResult->result;
                        $resultData['result_updated_by'] = auth()->id();
                    }
                    $this->practicalResult->update($resultData);

                    foreach ($data['criteria'] as $row) {
                        $assessment = ExamCriteriaAssessment::find($row['id']);
                        if (! $assessment) {
                            continue;
                        }

                        if ($assessment->result !== $row['result'] || $assessment->notes !== $row['notes']) {
                            $assessment->previous_result = $assessment->result;
                            $assessment->result_updated_by = auth()->id();
                        }
                        $assessment->update([
                            'result' => $row['result'],
                            'notes' => $row['notes'],
                        ]);
                    }

                    $this->practicalResult->refresh();
                    Notification::make()
                        ->title('Exam updated successfully.')
                        ->success()
                        ->send();
                }),
        ];
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

            Section::make('Exam Result')->schema([
                TextEntry::make('result')->label('Result')->badge()->color(fn ($state) => match ($state) {
                    'Passed' => 'success',
                    'Failed' => 'danger',
                    'Incomplete' => 'warning',
                    default => 'gray',
                })->getStateUsing(fn () => PracticalResult::resultHuman($this->practicalResult->result)),

                TextEntry::make('notes')->html()->extraAttributes(['style' => 'word-break:break-word'])->label('Additional Comments'),

                TextEntry::make('previous_result')->label('Previous Result')->badge()->color(fn ($state) => match ($state) {
                    'Passed' => 'success',
                    'Failed' => 'danger',
                    'Incomplete' => 'warning',
                    default => 'gray',
                })->getStateUsing(fn () => PracticalResult::resultHuman($this->practicalResult->previous_result))
                    ->visible(fn () => ! empty($this->practicalResult->previous_result)),

                TextEntry::make('result_updated_by')->label('Result Updated By')->getStateUsing(function () {
                    if ($this->practicalResult->result_updated_by) {
                        $user = Account::find($this->practicalResult->result_updated_by);

                        return $user ? $user->name : 'Unknown User';
                    }

                    return 'N/A';
                })->visible(fn () => ! empty($this->practicalResult->previous_result)),

                TextEntry::make('result_update_reason')->label('Internal Note')
                    ->helperText('This note is only visible to users who can edit exam results.')
                    ->visible(fn () => ! empty($this->practicalResult->previous_result) && auth()->user()->can('training.exams.override')),

            ])->columns(2)->extraAttributes(['class' => 'items-stretch']),
        ]);
    }

    public function criteriaInfoList(Infolist $infolist): Infolist
    {
        return $infolist->record($this->practicalResult)->schema([
            RepeatableEntry::make('criteria')->label('')->schema([
                TextEntry::make('examCriteria.criteria')->label(null)->columnSpan(fn ($record) => ! empty($record->previous_result) ? 8 : 10),
                PracticalExamCriteriaResult::make('result')->label('Result')->columnSpan(2),
                PracticalExamCriteriaResult::make('previous_result')->getStateUsing(fn ($record) => $record->previous_result)->label('Previous Result')->visible(fn ($record) => ! empty($record->previous_result))->columnSpan(2),
                TextEntry::make('notes')->html()->label('Notes')->columnSpan(12),
            ])->columns(12),
        ]);
    }
}
