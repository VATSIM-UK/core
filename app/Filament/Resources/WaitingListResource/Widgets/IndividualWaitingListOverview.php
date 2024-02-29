<?php

namespace App\Filament\Resources\WaitingListResource\Widgets;

use App\Models\Training\WaitingList\WaitingListStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class IndividualWaitingListOverview extends BaseWidget
{
    public ?Model $record = null;

    protected static ?string $pollingInterval = '1m';

    protected function getStats(): array
    {
        $eligibleAccounts = $this->record->eligibleAccounts()->count();
        $ineligibleAccounts = $this->record->ineligibleAccounts()->count();
        $totalAccounts = $eligibleAccounts + $ineligibleAccounts;

        $averageWaitTime = $this->record->accounts->average(fn ($account) => $account->pivot->created_at->diffInDays(now()));

        return [
            Stat::make('Total accounts', $totalAccounts),
            Stat::make('Eligible accounts', $eligibleAccounts),
            Stat::make('Ineligible accounts', $ineligibleAccounts),
            Stat::make('Average wait time', $averageWaitTime ? round($averageWaitTime).' days' : 'N/A'),
        ];
    }
}
