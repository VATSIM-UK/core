<?php

namespace App\Services\Roles;

use App\Models\Mship\Account;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

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
        Permission::where('name', $this->delegatePermissionName($role))->delete();
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

    public function getManageableRolesQuery(Builder $query, Account $user)
    {
        if ($user->can('account.edit-roles.*')) {
            return $query;
        }

        $manageableRoleIds = Role::all()->filter(function ($role) use ($user) {
        return $this->delegatePermissionExists($role)
            && $user->hasPermissionTo($this->delegatePermissionName($role));
        })->pluck('id');

        return $query->whereIn('id', $manageableRoleIds);
    }
}
