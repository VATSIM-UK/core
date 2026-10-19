<?php

namespace App\Filament\Training\Resources\Seminars\Widgets;

use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarStatisticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarOutcomeStatsOverview extends StatsOverviewWidget
{
    public ?Seminar $record = null;

    protected static bool $isLazy = false;

    protected int|array|null $columns = ['default' => 1, 'sm' => 2, 'lg' => 5];

    protected function getStats(): array
    {
        $seminar = $this->record;

        if (! $seminar) {
            return [];
        }

        $statistics = app(SeminarStatisticsService::class)->statisticsFor($seminar);

        return [
            Stat::make('Expired Invitations', $statistics['expired'])
                ->icon('heroicon-o-x-circle'),

            Stat::make('Cannot Attend', $statistics['cannot_attend'])
                ->icon('heroicon-o-hand-thumb-down'),

            Stat::make('Not Interested', $statistics['not_interested'])
                ->icon('heroicon-o-hand-raised'),

            Stat::make('Waiting List Removals', $statistics['removed_from_waiting_list'])
                ->icon('heroicon-o-user-minus'),

            Stat::make('Theory Reminders', $statistics['theory_reminders'])
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
