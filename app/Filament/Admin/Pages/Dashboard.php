<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    protected static ?string $title = 'Admin Panel';

    protected ?string $heading = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';
}
