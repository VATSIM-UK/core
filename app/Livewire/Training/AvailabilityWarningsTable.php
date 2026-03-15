<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\AvailabilityWarnings;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
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
            ->actions([
                ActionGroup::make([
                    Action::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Availability Warning')
                        ->modalDescription('Are you sure you want to delete this availability warning? This action cannot be undone. The linked availability check will be updated to passed.')
                        ->modalSubmitActionLabel('Delete')
                        ->modalCancelActionLabel('Cancel')
                        ->visible(fn () => auth()->user()?->can('training-places.availability-warnings.delete'))
                        ->action(fn (AvailabilityWarning $record) => AvailabilityWarnings::deleteWarning($record)),
                ]),
            ])
            ->emptyStateHeading('No availability warnings');
    }

    public function render()
    {
        return view('livewire.training.availability-warnings-table');
    }
}
