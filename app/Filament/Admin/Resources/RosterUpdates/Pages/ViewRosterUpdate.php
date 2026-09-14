<?php

namespace App\Filament\Admin\Resources\RosterUpdates\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Helpers\Pages\LogPageAccess;
use App\Filament\Admin\Resources\RosterUpdates\RosterUpdateResource;

class ViewRosterUpdate extends BaseViewRecordPage
{
    use LogPageAccess;

    protected static string $resource = RosterUpdateResource::class;

    protected function getLogActionName(): string
    {
        return 'ViewRosterUpdate';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
