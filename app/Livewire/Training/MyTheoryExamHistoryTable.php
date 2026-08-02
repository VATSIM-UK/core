<?php

namespace App\Livewire\Training;

use App\Filament\Training\Support\TheoryExamViewTrait;
use App\Repositories\Cts\TheoryExamResultRepository;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class MyTheoryExamHistoryTable extends Component implements HasActions, HasForms, HasInfolists, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithTable;
    use TheoryExamViewTrait;

    public function table(Table $table): Table
    {
        $repo = app(TheoryExamResultRepository::class);
        $user = auth()->user();

        return $table
            ->heading('Theory Exam History')
            ->query(
                $repo->getTheoryExamHistoryQueryForLevels(collect(['s1', 's2', 's3', 'c1']))->whereHas('student', fn ($q) => $q->where('cid', $user->id))
            )
            ->columns([
                TextColumn::make('exam_label')->label('Exam'),
                TextColumn::make('score')->label('Score')->getStateUsing(fn ($record) => "{$record->correct} / {$record->questions} (".round(($record->correct / $record->questions) * 100).'%)'),
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
                    ->label('View Report')
                    ->icon(null)
                    ->color('primary')
                    ->modalHeading(fn ($record) => (($record->student?->account?->name) ?? 'Unknown')."'s {$record->exam} Theory Exam")
                    ->infolist($this->theoryExamInfoList()),
            ]);
    }

    public function render()
    {
        return view('livewire.training.my-theory-exam-history-table');
    }
}
