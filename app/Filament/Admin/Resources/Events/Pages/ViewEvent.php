<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;
}
