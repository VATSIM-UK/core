<?php

namespace App\Filament\Training\Pages;

use App\Filament\Training\Actions\SubscribeToCalendarAction;
use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    protected static ?string $title = 'Training Panel';

    protected ?string $heading = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected function getHeaderActions(): array
    {
        return [
            SubscribeToCalendarAction::make(),
        ];
    }
}
