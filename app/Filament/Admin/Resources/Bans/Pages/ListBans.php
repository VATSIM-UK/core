<?php

namespace App\Filament\Admin\Resources\Bans\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Bans\BanResource;

class ListBans extends BaseListRecordsPage
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
