<?php

namespace App\Livewire\Training;

use App\Models\Cts\CancelReason;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class TrainingPlaceExamCancellationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        $position = $this->trainingPlace->trainingPosition->exam_callsign;

        return $table
            ->heading('Exam Cancellations')
            ->query($this->cancellationsQuery())
            ->columns([
                TextColumn::make('date')
                    ->label('Date/Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->tooltip(fn ($state) => $state)
                    ->wrap(),
                TextColumn::make('reason_by')
                    ->label('Cancelled By'),
            ])
            ->emptyStateHeading('No exam cancellations found for this training place.');
    }

    public function render()
    {
        return view('livewire.training.training-place-exam-cancellations-table');
    }

    public function hasExamCancellations(): bool
    {
        return $this->cancellationsQuery()->exists();
    }

    private function cancellationsQuery()
    {
        $position = $this->trainingPlace->trainingPosition->exam_callsign;

        return CancelReason::query()
            ->select('cancel_reason.*')
            ->join('exam_book', 'cancel_reason.sesh_id', '=', 'exam_book.id')
            ->where('cancel_reason.sesh_type', 'EX')
            ->where('exam_book.position_1', $position)
            ->orderBy('cancel_reason.date', 'desc');
    }
}
