<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\RoleResource;
use App\Services\Roles\DelegateRoleManagementService;
use Filament\Actions;
use Spatie\Permission\Models\Role;

class EditRole extends BaseEditRecordPage
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function delegatePermissionExists(Role $role): bool
    {
        $service = new DelegateRoleManagementService;

        return $service->delegatePermissionExists($role);
    }
}
