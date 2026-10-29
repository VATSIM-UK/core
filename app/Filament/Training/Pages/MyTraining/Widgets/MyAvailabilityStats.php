<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Models\Training\Availability;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyAvailabilityStats extends BaseWidget
{
    protected function getStats(): array
    {
        $accountId = $this->resolveAccountId();

        if (! $accountId) {
            return [];
        }

        $query = Availability::query()
            ->forAccount($accountId)
            ->endingAfter(now()->utc());

        $futureAvailability = $query->get();

        $futureSlotsCount = $futureAvailability->count();

        $totalMinutes = $futureAvailability->sum(function (Availability $availability) {
            return $availability->startsAt()->diffInMinutes($availability->endsAt());
        });

        $hours = round($totalMinutes / 60, 1);

        return [
            Stat::make('Upcoming Slots', $futureSlotsCount),
            Stat::make('Total Availability', "{$hours} Hours"),
        ];
    }

    protected function resolveAccountId(): ?int
    {
        return (int) auth()->id();
    }
}
