<?php

namespace App\Livewire\Training;

use App\Models\NetworkData\Atc;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\CarbonInterval;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class RecentControllingTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Controlling during training')
            ->queryStringIdentifier('recent-controlling')
            ->query(
                Atc::query()->where('account_id', $this->trainingPlace->waitingListAccount->account_id)
                    ->where('created_at', '>=', $this->trainingPlace->created_at)
                    ->isUk()
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Date')->date('d/m/Y H:i:s'),
                TextColumn::make('callsign')->label('Callsign'),
                TextColumn::make('duration')->label('Duration')->getStateUsing(function ($record) {
                    $minutes = $record->minutes_online ?? 0;
                    $interval = CarbonInterval::minutes($minutes)->cascade();

                    $parts = [];
                    if ($interval->hours > 0) {
                        $parts[] = "{$interval->hours} hours";
                    }
                    if ($interval->minutes > 0) {
                        $parts[] = "{$interval->minutes} minutes";
                    }

                    return $parts ? implode(' ', $parts) : '0 minutes';
                }),
            ])
            ->emptyStateHeading('No records found');
    }

    public function render()
    {
        return view('livewire.training.recent-controlling-table');
    }
}
