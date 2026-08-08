<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\AvailabilityLogEvent;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class AvailabilityLogTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    private ?Collection $successorCreatedAts = null;

    public function mount(): void
    {
        if (! auth()->user()?->can('view', $this->trainingPlace)) {
            abort(403);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('availability-log')
            ->query(
                AvailabilityLogEntry::query()
                    ->where('training_place_id', $this->trainingPlace->id)
                    ->orderBy('created_at', 'desc')
            )
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d.m.Y H:i'),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn (AvailabilityLogEntry $record) => $record->event->label())
                    ->color(fn (AvailabilityLogEntry $record) => match ($record->event) {
                        AvailabilityLogEvent::Added => 'success',
                        AvailabilityLogEvent::Merged => 'warning',
                        AvailabilityLogEvent::Edited => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('slot')
                    ->label('Slot')
                    ->state(fn (AvailabilityLogEntry $record) => $record->slot_from->format('d.m.Y H:i').' - '.$record->slot_to->format('H:i')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (AvailabilityLogEntry $record) => $this->logEntryStatus($record))
                    ->color(fn (string $state) => match ($state) {
                        'Current' => 'success',
                        'Changed' => 'info',
                        'Removed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->emptyStateHeading('No availability logged yet');
    }

    /**
     * The created_at timestamps of merged/edited successors for this training place,
     * memoized so the status column does not query once per row.
     */
    private function successorCreatedAts(): Collection
    {
        return $this->successorCreatedAts ??= AvailabilityLogEntry::query()
            ->where('training_place_id', $this->trainingPlace->id)
            ->whereIn('event', [AvailabilityLogEvent::Merged, AvailabilityLogEvent::Edited])
            ->pluck('created_at');
    }

    public function logEntryStatus(AvailabilityLogEntry $entry): string
    {
        if ($entry->superseded_at === null) {
            return 'Current';
        }

        // A successor is the next version of the same slot, so its created_at equals this
        // version's superseded_at (the write-wiring stamps both with one shared now()).
        // This match is unambiguous because the UI serializes a student's actions - two
        // mutations to one training place within the same wall-clock second do not occur.
        return $this->successorCreatedAts()->contains($entry->superseded_at->format('Y-m-d H:i:s'))
            ? 'Changed'
            : 'Removed';
    }

    public function render()
    {
        return view('livewire.training.availability-log-table');
    }
}
