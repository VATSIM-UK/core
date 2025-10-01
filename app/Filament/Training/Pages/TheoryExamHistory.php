<?php

namespace App\Filament\Training\Pages;

use App\Repositories\Cts\TheoryExamResultRepository;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
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

        return auth()->user()->can('training.exams.access');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Exam Information')
                    ->schema([
                        Placeholder::make('cid')->label('CID')->content(fn ($record) => $record->student_id),

                        Placeholder::make('Name')->label('Name')->content(fn ($record) => $record->student?->account?->name),

                        Placeholder::make('Exam')->label('Exam')->content(fn ($record) => $record->exam),

                        Placeholder::make('Result')->label('Result')->content(fn ($record) => $record->resultHuman()),
                    ]),

                Fieldset::make('Details')
                    ->schema([
                        Placeholder::make('started')->label('Started')->content(fn ($record) => $record->started),
                        Placeholder::make('submitted_time')->label('Submitted Time')->content(fn ($record) => $record->submitted_time),
                        Placeholder::make('score')->label('Score')->content(fn ($record) => "{$record->correct} / {$record->questions} (Passmark: {$record->passmark})"),
                        Placeholder::make('time_mins')->label('Time Limit')->content(fn ($record) => "{$record->time_mins} Mins"),
                        Placeholder::make('expires')->label('Expires')->content(fn ($record) => $record->expires),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $userPermissionsTruthTable = [
            's1' => auth()->user()->can('training.exams.conduct.obs'),
            's2' => auth()->user()->can('training.exams.conduct.twr'),
            's3' => auth()->user()->can('training.exams.conduct.app'),
            'c1' => auth()->user()->can('training.exams.conduct.ctr'),
        ];

        $typesToShow = collect($userPermissionsTruthTable)->filter(fn ($value) => $value)->keys();

        $theoryExamResultRepository = app(TheoryExamResultRepository::class);
        $query = $theoryExamResultRepository->getTheoryExamHistoryQueryForLevels($typesToShow);

        return $table->query($query)->columns([
            TextColumn::make('student_id')->label('CID')->searchable(),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                'Passed' => 'success',
                'Failed' => 'danger',
                default => 'gray',
            })->label('Result'),
            TextColumn::make('submitted_time')->label('Submitted'),
        ])->defaultSort('submitted_time', 'desc')
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->modalHeading(fn ($record) => "{$record->student?->account?->name}'s {$record->exam} Theory Exam")
                    ->form(fn (Form $form) => $this->form($form)),
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
                Filter::make('position')->form([
                    Forms\Components\Select::make('position')
                        ->options([
                            'S1' => 'Observer',
                            'S2' => 'Tower',
                            'S3' => 'Approach',
                            'C1' => 'Enroute',
                        ])
                        ->multiple()
                        ->label('Position'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['position'], fn ($query, $positions) => $query->where(function ($subQuery) use ($positions) {
                            foreach ($positions as $position) {
                                $subQuery->orWhere('exam', 'LIKE', "%{$position}%");
                            }
                        })
                        );
                })->label('Position'),
            ])
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }
}
