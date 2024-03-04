<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\AccountResource;

class ListAccounts extends BaseListRecordsPage
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
