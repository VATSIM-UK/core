<?php

namespace App\Filament\Admin\Resources\BanResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\BanResource;

class ListBans extends BaseListRecordsPage
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
