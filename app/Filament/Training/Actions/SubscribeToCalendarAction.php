<?php

declare(strict_types=1);

namespace App\Filament\Training\Actions;

use Filament\Actions\Action;

class SubscribeToCalendarAction
{
    public static function make(): Action
    {
        return Action::make('subscribeToCalendar')
            ->label('Subscribe to your bookings')
            ->icon('heroicon-m-calendar-days')
            ->color('gray')
            ->url(route('site.bookings.calendar.subscribe'))
            ->openUrlInNewTab();
    }
}
