<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    protected static ?string $title = 'Training Panel';

    protected ?string $heading = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';
}
