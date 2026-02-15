<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use Spatie\Permission\Models\Role;
use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\RoleResource;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use App\Models\Permission;
use Filament\Notifications\Notification;
use App\Models\Mship\Account;
use Filament\Forms\Components\Select;
use App\Services\Roles\DelegateRoleManagementService;

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
        $service = new DelegateRoleManagementService();
        return $service->delegatePermissionExists($role);
    }
}