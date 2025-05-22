<?php

namespace App\Filament\Resources\WaitingListResource\Widgets;

use App\Models\Training\WaitingList;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IndividualWaitingListOverview extends BaseWidget
{
    public ?WaitingList $record = null;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalAccounts = $this->record->waitingListAccounts()->count();

        $averageWaitTime = $this->record->waitingListAccounts->average(fn ($listAccount) => $listAccount->created_at->diffInDays(now()));
        $longestWaitTime = $this->record->waitingListAccounts->max(fn ($listAccount) => $listAccount->created_at->diffInDays(now()));

        return [
            Stat::make('Total accounts', $totalAccounts),
            Stat::make('Longest wait time', $longestWaitTime ? round($longestWaitTime).' days' : 'N/A'),
            Stat::make('Average wait time', $averageWaitTime ? round($averageWaitTime).' days' : 'N/A'),
        ];
    }
}
