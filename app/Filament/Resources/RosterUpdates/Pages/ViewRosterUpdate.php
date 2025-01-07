<?php

namespace App\Filament\Resources\RosterUpdates\Pages;

use App\Filament\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Resources\RosterUpdateResource;

class ViewRosterUpdate extends BaseViewRecordPage
{
    protected static string $resource = RosterUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
