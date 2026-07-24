<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationOverviewWidget extends StatsOverviewWidget
{
    public ?int $year = null;

    public ?int $type = null;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    protected function getStats(): array
    {
        $year = $this->year ?? now()->year;
        $type = $this->type;

        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        $totals = VisitTransferStats::totals($this->type, $this->start, $this->end);
        $avgDays = VisitTransferStats::averageDaysToDecision($this->type, $this->start, $this->end);

        return [
            Stat::make('Total Applications', $totals['total'])->color('gray'),

            Stat::make('Awaiting Action', $totals['submitted'] + $totals['under_review'])
                ->description("{$totals['under_review']} under review, {$totals['submitted']} submitted")
                ->color('warning'),

            Stat::make('Accepted', $totals['accepted'] + $totals['completed'])
                ->description("{$totals['completed']} completed")
                ->color('success'),

            Stat::make('Rejected / Cancelled', $totals['rejected'] + $totals['cancelled'])
                ->description("{$totals['rejected']} rejected, {$totals['cancelled']} cancelled")
                ->color('danger'),

            Stat::make('Acceptance Rate', $totals['acceptance_rate'] !== null ? "{$totals['acceptance_rate']}%" : '—')
                ->description('Of decided applications')
                ->color('primary'),

            Stat::make('Avg. Days to Decision', $avgDays ?? '—')
                ->description('Submission to accept/reject')
                ->color('info'),
        ];
    }
}
