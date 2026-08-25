<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class AvailabilityLogReview extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public array $data = [];

    public function mount(): void
    {
        if (! auth()->user()?->can('view', $this->trainingPlace)) {
            abort(403);
        }

        $this->form->fill([
            'asOf' => now()->format('Y-m-d H:i'),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            DateTimePicker::make('asOf')
                ->label('Availability as of')
                ->timezone('UTC')
                ->seconds(false)
                ->native(false)
                ->displayFormat('d.m.Y H:i')
                ->live()
                ->maxDate(now()),
        ])->statePath('data');
    }

    public function asOfCarbon(): ?Carbon
    {
        if (blank($this->data['asOf'] ?? null)) {
            return null;
        }

        try {
            $asOf = Carbon::parse($this->data['asOf']);
        } catch (\Throwable) {
            return null;
        }

        return $asOf->isAfter(now()) ? now() : $asOf;
    }

    public function setAsOfToNow(): void
    {
        $this->data['asOf'] = now()->format('Y-m-d H:i');
    }

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('availability-log-snapshot')
            ->query(function (): Builder {
                $asOf = $this->asOfCarbon();

                if (! $asOf) {
                    return AvailabilityLogEntry::query()->whereRaw('1 = 0');
                }

                return AvailabilityLogEntry::query()
                    ->where('training_place_id', $this->trainingPlace->id)
                    ->where('created_at', '<=', $asOf)
                    ->where(function (Builder $query) use ($asOf) {
                        $query->whereNull('superseded_at')
                            ->orWhere('superseded_at', '>', $asOf);
                    })
                    ->orderBy('slot_from');
            })
            ->paginated(false)
            ->columns([
                TextColumn::make('day')
                    ->label('Day')
                    ->state(fn (AvailabilityLogEntry $record) => $record->slot_from->format('d.m.Y')),

                TextColumn::make('time')
                    ->label('Time (Zulu)')
                    ->fontFamily('mono')
                    ->state(fn (AvailabilityLogEntry $record) => $record->slot_from->format('H:i').' - '.$record->slot_to->format('H:i')),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn (AvailabilityLogEntry $record) => $this->slotDuration($record)),
            ])
            ->emptyStateHeading('No availability at this time');
    }

    private function slotDuration(AvailabilityLogEntry $entry): string
    {
        $minutes = (int) $entry->slot_from->diffInMinutes($entry->slot_to);

        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $remaining === 0 ? "{$hours}h" : "{$hours}h {$remaining}m";
    }

    public function render()
    {
        return view('livewire.training.availability-log-review');
    }
}
