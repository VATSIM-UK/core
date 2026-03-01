<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\AvailabilityCheckStatus;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AvailabilityChecksTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Availability checks')
            ->queryStringIdentifier('availability-checks')
            ->query(
                AvailabilityCheck::query()
                    ->where('training_place_id', $this->trainingPlace->id)
                    ->orderBy('created_at', 'desc')
            )
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Day')
                    ->date('d/m/Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AvailabilityCheck $record) => $record->status->label())
                    ->color(fn ($state, AvailabilityCheck $record) => match ($record->status) {
                        AvailabilityCheckStatus::Passed => 'success',
                        AvailabilityCheckStatus::Failed => 'danger',
                        AvailabilityCheckStatus::OnLeave => 'info',
                    }),
            ])
            ->emptyStateHeading('No availability checks');
    }

    public function render()
    {
        return view('livewire.training.availability-checks-table');
    }
}
