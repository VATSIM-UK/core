<?php

namespace App\Filament\Training\Pages\TrainingPlace\Widgets;

use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class TrainingPlaceStatsWidget extends BaseWidget
{
    public ?TrainingPlace $trainingPlace = null;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        if (! $this->trainingPlace) {
            return [];
        }

        $daysActive = (int) ceil($this->trainingPlace->created_at->diffInRealDays(now()));
        $activeTime = "{$daysActive} ".Str::plural('day', $daysActive);

        $waitingTime = (int) ceil($this->trainingPlace->waitingListAccount?->created_at->diffInDays($this->trainingPlace->waitingListAccount->deleted_at));
        $waitingTime = "{$waitingTime} ".Str::plural('day', $waitingTime);

        return [
            Stat::make('Training Time', $activeTime)
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Waiting Time in Queue', $waitingTime)
                ->icon('heroicon-o-clock')
                ->color('primary'),
        ];
    }
}
