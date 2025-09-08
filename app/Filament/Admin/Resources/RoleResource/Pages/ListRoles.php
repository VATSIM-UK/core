<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\RoleResource;
use Filament\Actions\CreateAction;

class ListRoles extends BaseListRecordsPage
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
