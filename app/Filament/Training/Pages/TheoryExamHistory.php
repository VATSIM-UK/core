<?php

namespace App\Filament\Training\Pages;

use App\Repositories\Cts\TheoryExamResultRepository;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TheoryExamHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.theory-exam-history';

    protected static ?string $navigationGroup = 'Theory';

    public static function canAccess(): bool
    {

        return auth()->user()->can('training.theory.access');
    }

    protected function buildQuestionPlaceholders($record): array
    {

        if (! $record) {
            return [];
        }

        $answers = $record->answers()->with('question')->get();

        return $answers->map(function ($answer, $index) use ($record) {
            $number = $index + 1;
            $question = $answer->question;
            $questionText = $question->question ?? 'Unknown question';

            $givenAnswer = $record->getOptionText($question, $answer->answer_given);
            $correctAnswer = $record->getOptionText($question, $question->answer ?? null);

            $isCorrect = $answer->answer_given == ($question->answer ?? null);

            return Fieldset::make("Question {$number}")
                ->schema([
                    TextEntry::make("question_{$number}_text")
                        ->label('Question')
                        ->getStateUsing($questionText),
                    TextEntry::make("question_{$number}_answer")
                        ->label('Member Answer')
                        ->getStateUsing($givenAnswer),
                    TextEntry::make("question_{$number}_correct")
                        ->label('Correct Answer')
                        ->getStateUsing($correctAnswer),
                    TextEntry::make("question_{$number}_status")
                        ->label('Status')
                        ->badge()
                        ->color($isCorrect ? 'success' : 'danger')
                        ->getStateUsing($isCorrect ? 'CORRECT' : 'INCORRECT'),
                ])
                ->columns(4);
        })->all();
    }

    public function table(Table $table): Table
    {
        $userPermissionsTruthTable = [
            's1' => auth()->user()->can('training.theory.view.obs'),
            's2' => auth()->user()->can('training.theory.view.twr'),
            's3' => auth()->user()->can('training.theory.view.app'),
            'c1' => auth()->user()->can('training.theory.view.ctr'),
        ];

        $typesToShow = collect($userPermissionsTruthTable)->filter(fn ($value) => $value)->keys();

        $theoryExamResultRepository = app(TheoryExamResultRepository::class);
        $query = $theoryExamResultRepository->getTheoryExamHistoryQueryForLevels($typesToShow);

        return $table->query($query)->columns([
            TextColumn::make('student.cid')->label('CID')->searchable(),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                'Passed' => 'success',
                'Failed' => 'danger',
                default => 'gray',
            })->label('Result'),
            TextColumn::make('submitted_time')->label('Submitted')->isoDateTimeFormat('lll'),
        ])->defaultSort('submitted_time', 'desc')
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->icon(null)
                    ->color('primary')
                    ->modalHeading(fn ($record) => (($record->student?->account?->name) ?? 'Unknown')."'s {$record->exam} Theory Exam")
                    ->infoList([
                        Fieldset::make('Exam Information')
                            ->schema([
                                TextEntry::make('cid')->label('CID')->getStateUsing(fn ($record) => $record->student_id),

                                TextEntry::make('Name')->label('Name')->getStateUsing(fn ($record) => $record->student?->account?->name ?? 'Unknown'),

                                TextEntry::make('Exam')->label('Exam')->getStateUsing(fn ($record) => $record->exam),

                                TextEntry::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                                    'Passed' => 'success',
                                    'Failed' => 'danger',
                                })->label('Result'),
                            ]),

                        Fieldset::make('Details')
                            ->schema([
                                TextEntry::make('started')->label('Started')->getStateUsing(fn ($record) => Carbon::parse($record->started)->isoFormat('lll')),
                                TextEntry::make('submitted_time')->label('Submitted Time')->getStateUsing(fn ($record) => $record->submitted_time ? Carbon::parse($record->submitted_time)->isoFormat('lll') : 'N/A'), // Some exams will not be submitted if they run out of time etc
                                TextEntry::make('score')->label('Score')->getStateUsing(fn ($record) => "{$record->correct} / {$record->questions} (Passmark: {$record->passmark})"),
                                TextEntry::make('time_mins')->label('Time Limit')->getStateUsing(fn ($record) => "{$record->time_mins} Mins"),
                            ]),

                        Section::make('Questions')->collapsible()->collapsed()->schema(fn ($record) => $this->buildQuestionPlaceholders($record)),
                    ]),
            ])
            ->filters([
                Filter::make('exam_date')->form([
                    Forms\Components\DatePicker::make('exam_date_from')->label('From'),
                    Forms\Components\DatePicker::make('exam_date_to')->label('To'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['exam_date_from'], fn ($query, $date) => $query->whereDate('submitted_time', '>=', $date))
                        ->when($data['exam_date_to'], fn ($query, $date) => $query->whereDate('submitted_time', '<=', $date));
                })->label('Exam date'),
                Filter::make('exam_rating')->form([
                    Forms\Components\Select::make('exam_rating')
                        ->options([
                            'S1' => 'OBS/Student (S1)',
                            'S2' => 'Tower (S2)',
                            'S3' => 'Approach (S3)',
                            'C1' => 'Enroute (C1)',
                        ])
                        ->multiple()
                        ->label('Exam'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['exam_rating'], fn ($query, $exam_ratings) => $query->where(function ($subQuery) use ($exam_ratings) {
                            foreach ($exam_ratings as $exam_rating) {
                                $subQuery->orWhere('exam', 'LIKE', "%{$exam_rating}%");
                            }
                        })
                        );
                })->label('Exam'),
            ])
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }
}
