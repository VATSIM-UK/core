<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
}
