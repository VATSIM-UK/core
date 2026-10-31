<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\Concerns\SyncsRoleStaffPosition;
use App\Filament\Admin\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use SyncsRoleStaffPosition;

    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $this->syncRoleStaffPosition();
    }
}
