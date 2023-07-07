<?php

namespace App\Filament\Resources\FeedbackResource\Widgets;

use App\Models\Mship\Feedback\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class FeedbackOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make("Total Feedback", Feedback::count()),
        ];
    }
}
