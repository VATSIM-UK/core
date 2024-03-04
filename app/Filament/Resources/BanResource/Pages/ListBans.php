<?php

namespace App\Filament\Resources\BanResource\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\BanResource;

class ListBans extends BaseListRecordsPage
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
