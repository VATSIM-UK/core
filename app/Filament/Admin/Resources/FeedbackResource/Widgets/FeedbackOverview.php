<?php

namespace App\Filament\Admin\Resources\FeedbackResource\Widgets;

use App\Models\Mship\Feedback\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedbackOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        $totalFeedback = Feedback::count();

        return [
            Stat::make('Total feedback', $totalFeedback),
            Stat::make('% feedback actioned', ($totalFeedback ? round(Feedback::whereNotNull('actioned_at')->count() / $totalFeedback * 100) : 100).'%'),
        ];
    }
}
