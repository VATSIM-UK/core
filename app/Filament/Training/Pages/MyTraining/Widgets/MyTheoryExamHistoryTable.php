<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Filament\Training\Support\TheoryExamViewTrait;
use App\Repositories\Cts\TheoryExamResultRepository;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyTheoryExamHistoryTable extends BaseWidget
{
    use TheoryExamViewTrait;

    protected static ?string $heading = 'Theory Exam History';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $id = 'my-theory-exam-history-table';

    public function table(Table $table): Table
    {
        $repo = app(TheoryExamResultRepository::class);
        $user = auth()->user();

        return $table
            ->query(
                $repo->getTheoryExamHistoryQueryForLevels(collect(['s1', 's2', 's3', 'c1']))->whereHas('student', fn ($q) => $q->where('cid', $user->id))
            )
            ->columns([
                TextColumn::make('exam')->label('Exam'),
                TextColumn::make('score')->label('Score')->getStateUsing(fn ($record) => "{$record->correct} / {$record->questions}"),
                TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                    'Passed' => 'success',
                    'Failed' => 'danger',
                    default => 'gray',
                })->label('Result'),
                TextColumn::make('submitted_time')->label('Exam date')->isoDateTimeFormat('lll'),
            ])
            ->defaultSort('submitted_time', 'desc')
            ->recordActions([
                ViewAction::make('view')
                    ->label('View')
                    ->icon(null)
                    ->color('primary')
                    ->modalHeading(fn ($record) => (($record->student?->account?->name) ?? 'Unknown')."'s {$record->exam} Theory Exam")
                    ->infolist($this->theoryExamInfoList()),
            ]);
    }
}
