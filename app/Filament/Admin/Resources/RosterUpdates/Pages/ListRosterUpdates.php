<?php

namespace App\Filament\Admin\Resources\RosterUpdates\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\RosterUpdateResource;

class ListRosterUpdates extends BaseListRecordsPage
{
    protected static string $resource = RosterUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
