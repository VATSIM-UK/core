<?php

namespace App\Filament\Resources\RosterUpdates\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\RosterUpdateResource;

class ListRosterUpdates extends BaseListRecordsPage
{
    protected static string $resource = RosterUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
