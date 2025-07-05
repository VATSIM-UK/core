<?php

namespace App\Filament\Admin\Resources\RosterUpdates\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\RosterUpdateResource;

class ViewRosterUpdate extends BaseViewRecordPage
{
    protected static string $resource = RosterUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
