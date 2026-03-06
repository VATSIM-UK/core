<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AvailabilityWarningsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Availability warnings')
            ->queryStringIdentifier('availability-warnings')
            ->query(
                AvailabilityWarning::query()
                    ->where('training_place_id', $this->trainingPlace->id)
                    ->with(['availabilityCheck', 'resolvedAvailabilityCheck'])
                    ->orderBy('created_at', 'desc')
            )
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Raised')
                    ->date('d/m/Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AvailabilityWarning $record) => match ($record->status) {
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'expired' => 'Expired',
                        default => $record->status,
                    })
                    ->color(fn ($state, AvailabilityWarning $record) => match ($record->status) {
                        'Pending' => 'warning',
                        'Resolved' => 'success',
                        'Expired' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date('d/m/Y'),

                TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->emptyStateHeading('No availability warnings');
    }

    public function render()
    {
        return view('livewire.training.availability-warnings-table');
    }
}
