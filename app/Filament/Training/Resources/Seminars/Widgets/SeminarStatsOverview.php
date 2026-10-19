<?php

namespace App\Filament\Training\Resources\Seminars\Widgets;

use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarStatisticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarStatsOverview extends StatsOverviewWidget
{
    public ?Seminar $record = null;

    protected static bool $isLazy = false;

    protected int|array|null $columns = ['default' => 1, 'sm' => 2, 'lg' => 4];

    protected function getStats(): array
    {
        $seminar = $this->record;

        if (! $seminar) {
            return [];
        }

        $statistics = app(SeminarStatisticsService::class)->statisticsFor($seminar);

        return [
            Stat::make('Total Invitations Sent', $statistics['invitations_sent'])
                ->icon('heroicon-o-paper-airplane'),

            Stat::make('Confirmed Attendees', $statistics['confirmed_attendees'])
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->description("{$statistics['confirmed_attendees']} / {$seminar->capacity} places filled"),

            Stat::make('Pending Invitations', $statistics['pending_responses'])
                ->icon('heroicon-o-clock')
                ->color($statistics['pending_responses'] > 0 ? 'info' : 'gray'),

            Stat::make('Declined Invitations', $statistics['declined_invitations'])
                ->icon('heroicon-o-hand-thumb-down')
                ->color($statistics['declined_invitations'] > 0 ? 'danger' : 'gray'),
        ];
    }
}
