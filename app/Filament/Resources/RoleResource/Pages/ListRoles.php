<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\RoleResource;
use Filament\Actions;

class ListRoles extends BaseListRecordsPage
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
