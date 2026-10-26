<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyAvailabilityStats extends BaseWidget
{
    protected function getStats(): array
    {
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return [];
        }

        $query = Availability::query()
            ->forStudent($studentId)
            ->studentOnly()
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

    protected function resolveStudentId(): ?int
    {
        return Member::where('cid', auth()->id())->value('id');
    }
}
