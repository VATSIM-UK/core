<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
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
            ->where('student_id', $studentId)
            ->where('type', 'S');

        $futureSlotsCount = (clone $query)
            ->where('date', '>=', now()->toDateString())
            ->count();

        $totalMinutes = (clone $query)
            ->where('date', '>=', now()->toDateString())
            ->get()
            ->sum(function ($availability) {
                return Carbon::parse($availability->from)->diffInMinutes(Carbon::parse($availability->to));
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
