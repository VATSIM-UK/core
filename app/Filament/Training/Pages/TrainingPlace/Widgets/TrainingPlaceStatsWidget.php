<?php

namespace App\Filament\Training\Pages\TrainingPlace\Widgets;

use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class TrainingPlaceStatsWidget extends BaseWidget
{
    public ?TrainingPlace $trainingPlace = null;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        if (! $this->trainingPlace) {
            return [];
        }

        $daysActive = (int) ceil($this->trainingPlace->created_at->diffInRealDays(now()));
        $activeTime = "{$daysActive} ".Str::plural('day', $daysActive);

        $waitingListAccount = $this->trainingPlace->waitingListAccount;

        if (! $waitingListAccount) {
            $waitingTime = 'N/A';
        } else {
            $waitingListJoinDate = $waitingListAccount->created_at;

            $waitingListEndDate = $waitingListAccount->deleted_at ?? now();

            $waitingTimeDays = (int) ceil($waitingListJoinDate->diffInDays($waitingListEndDate));
            $waitingTime = "{$waitingTimeDays} ".Str::plural('day', $waitingTimeDays);
        }

        $warningsCount = $this->trainingPlace->availabilityWarnings()->count();

        return [
            Stat::make('Training Time', $activeTime)
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Waiting Time in Queue', $waitingTime)
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Availability Warnings', $warningsCount)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($warningsCount > 0 ? 'warning' : 'success'),
        ];
    }
}
