<?php

namespace App\Filament\Resources\FeedbackResource\Widgets;

use App\Models\Mship\Feedback\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class FeedbackOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        return [
            Card::make('Total Feedback', Feedback::count()),
            Card::make('% Feedback Actioned', round(max(Feedback::whereNotNull('actioned_at')->count(), 1) / Feedback::count() * 100).'%')
                ->description('Total percentage of feedback actioned'),
        ];
    }
}
