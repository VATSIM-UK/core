<?php

namespace App\Filament\Resources\FeedbackResource\Widgets;

use App\Models\Mship\Feedback\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedbackOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        return [
            Stat::make('Total feedback', Feedback::count()),
            Stat::make('% feedback actioned', round(max(Feedback::whereNotNull('actioned_at')->count(), 1) / max(Feedback::count(), 1) * 100).'%')
                ->description('Total percentage of feedback actioned'),
        ];
    }
}
