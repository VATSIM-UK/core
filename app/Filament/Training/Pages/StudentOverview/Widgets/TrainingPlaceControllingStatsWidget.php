<?php

namespace App\Filament\Training\Pages\StudentOverview\Widgets;

use App\Models\NetworkData\Atc;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingPlaceControllingStatsWidget extends BaseWidget
{
    public ?TrainingPlace $trainingPlace = null;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        if (! $this->trainingPlace) {
            return [];
        }

        $accountId = $this->trainingPlace->account_id;
        $startDate = $this->trainingPlace->created_at;
        $endDate = $this->trainingPlace->deleted_at?->copy() ?? now();

        $atcSessions = Atc::where('account_id', $accountId)
            ->whereBetween('connected_at', [$startDate, $endDate])
            ->get();

        $totalHours = $atcSessions->sum(fn ($session) => $session->minutes_online / 60);

        $weeksSinceStart = max(1, $startDate->diffInWeeks(now()));
        $avgHoursPerWeek = $totalHours / $weeksSinceStart;

        $lastDate = $atcSessions->sortByDesc('connected_at')->first()?->connected_at;

        return [
            Stat::make('Total Hours', round($totalHours, 1).'h')
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Hours / Week', round($avgHoursPerWeek, 1).'h')
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),

            Stat::make('Last Controlling Session', $lastDate?->format('d/m/Y') ?? 'Never')
                ->icon('heroicon-o-calendar-date-range')
                ->color($lastDate ? 'primary' : 'gray'),
        ];
    }
}
