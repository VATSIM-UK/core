<?php

namespace App\Livewire\Training;

use App\Models\NetworkData\Atc;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class ControllingCallsignTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('controlling-callsigns')
            ->records(fn (): Collection => $this->getCallsignBreakdown())
            ->columns([
                TextColumn::make('callsign')
                    ->label('Position')
                    ->sortable(),
                TextColumn::make('hours')
                    ->label('Hours')
                    ->formatStateUsing(fn ($state) => number_format($state, 1).'h')
                    ->sortable(),
                TextColumn::make('sessions')
                    ->label('Sessions')
                    ->sortable(),
            ])
            ->defaultSort('hours', 'desc');
    }

    private function getCallsignBreakdown(): Collection
    {
        $accountId = $this->trainingPlace->account_id;
        $startDate = $this->trainingPlace->created_at;
        $endDate = $this->trainingPlace->deleted_at?->copy() ?? now();

        return Atc::where('account_id', $accountId)
            ->whereBetween('connected_at', [$startDate, $endDate])
            ->get()
            ->groupBy('callsign')
            ->map(fn ($sessions, $callsign) => [
                'callsign' => $callsign,
                'hours' => round($sessions->sum('minutes_online') / 60, 1),
                'sessions' => $sessions->count(),
            ])
            ->values()
            ->sortByDesc('hours');
    }
}
