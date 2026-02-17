<?php

namespace App\Services\Roles;

use App\Models\Mship\Account;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DelegateRoleManagementService
{
    public function delegatePermissionName(Role $role): string
    {
        return "account.edit-roles.{$role->id}";
    }

    public function delegatePermissionExists(Role $role): bool
    {
        return Permission::where('name', $this->delegatePermissionName($role))->exists();
    }

    public function createDelegatePermission(Role $role): void
    {
        Permission::firstOrCreate([
            'name' => $this->delegatePermissionName($role),
            'guard_name' => 'web',
        ]);
    }

    public function deleteDelegatePermission(Role $role): void
    {
        $permission = Permission::where('name', $this->delegatePermissionName($role))->first();

        if (! $permission) {
            return;
        }

        $accounts = Account::whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission->name);
        })->get();

        foreach ($accounts as $account) {
            $account->revokePermissionTo($permission);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permission->delete();
    }

    public function revokeDelegate(Account $account, Role $role): void
    {
        $permissionName = $this->delegatePermissionName($role);
        $account->revokePermissionTo($permissionName);
    }

    public function getDelegates(Role $role)
    {
        $permissionName = $this->delegatePermissionName($role);

        return Account::query()->whereHas('permissions', function ($q) use ($permissionName) {
            $q->where('name', $permissionName);
        });
    }
}
