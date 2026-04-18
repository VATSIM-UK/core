<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyAvailabilityStats extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return [];
        }

        $today = now()->startOfDay();
        $now = now();
        $endOfWeek = now()->endOfWeek();
        $nextFourteenDays = now()->copy()->addDays(14)->endOfDay();

        $upcoming = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->where(function ($query) use ($now): void {
                $query->whereDate('date', '>', $now->toDateString())
                    ->orWhere(function ($query) use ($now): void {
                        $query->whereDate('date', '=', $now->toDateString())
                            ->whereTime('from', '>', $now->format('H:i:s'));
                    });
            })
            ->orderBy('date')
            ->orderBy('from')
            ->get();

        $upcomingMinutes = $upcoming->sum(function (Availability $availability): int {
            $start = Carbon::parse($availability->date->format('Y-m-d').' '.$availability->from->format('H:i:s'));
            $end = Carbon::parse($availability->date->format('Y-m-d').' '.$availability->to->format('H:i:s'));

            return max(0, $start->diffInMinutes($end));
        });

        $weeklyMinutes = $upcoming
            ->filter(fn (Availability $availability): bool => $availability->date->betweenIncluded($today, $endOfWeek))
            ->sum(function (Availability $availability): int {
                $start = Carbon::parse($availability->date->format('Y-m-d').' '.$availability->from->format('H:i:s'));
                $end = Carbon::parse($availability->date->format('Y-m-d').' '.$availability->to->format('H:i:s'));

                return max(0, $start->diffInMinutes($end));
            });

        $nextSlot = $upcoming->first();
        $nextSlotLabel = $nextSlot
            ? $nextSlot->date->format('D j M').' '.$nextSlot->from->format('H:i').' - '.$nextSlot->to->format('H:i').'Z'
            : 'No upcoming slots';

        $daysCoveredIn14Days = $upcoming
            ->filter(fn (Availability $availability): bool => $availability->date->betweenIncluded($today, $nextFourteenDays))
            ->groupBy(fn (Availability $availability): string => $availability->date->format('Y-m-d'))
            ->count();

        return [
            Stat::make('Upcoming Hours', number_format($upcomingMinutes / 60, 1).'h')
                ->description('Total hours across all future slots')
                ->icon('heroicon-o-calendar')
                ->color('gray'),
            Stat::make('Hours This Week', number_format($weeklyMinutes / 60, 1).'h')
                ->description('Total availability until Sunday')
                ->icon('heroicon-o-clock')
                ->color('gray'),
            Stat::make('Days Covered', (string) $daysCoveredIn14Days)
                ->description('Days with availability in next 14 days')
                ->icon('heroicon-o-chart-bar-square')
                ->color('gray'),
        ];
    }

    protected function resolveStudentId(): ?int
    {
        $cid = auth()->id();

        if (! $cid) {
            return null;
        }

        return Member::query()
            ->where('cid', $cid)
            ->value('id');
    }
}
