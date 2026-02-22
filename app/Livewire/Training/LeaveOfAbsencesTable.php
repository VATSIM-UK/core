<?php

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class LeaveOfAbsencesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Leaves of Absence')
            ->queryStringIdentifier('leave-of-absences')
            ->query(TrainingPlaceLeaveOfAbsence::query()->where('training_place_id', $this->trainingPlace->id))
            ->defaultSort('begins_at', 'desc')
            ->columns([
                TextColumn::make('begins_at')
                    ->label('Start')
                    ->date('d/m/Y'),

                TextColumn::make('ends_at')
                    ->label('End')
                    ->date('d/m/Y'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->getStateUsing(fn (TrainingPlaceLeaveOfAbsence $record) => $record->isActive())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(60)
                    ->tooltip(fn (TrainingPlaceLeaveOfAbsence $record) => $record->reason),
            ])
            ->emptyStateHeading('No leaves of absences');
    }

    public function render()
    {
        return view('livewire.training.leave-of-absences-table');
    }
}