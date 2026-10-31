<?php

namespace App\Services\Roles;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Builder;
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

        audit('Delegate permission changed', [
            'role_id' => $role->id,
            'action' => 'granted',
        ]);
    }

    public function deleteDelegatePermission(Role $role): void
    {
        $deleted = Permission::where('name', $this->delegatePermissionName($role))->delete();

        if ($deleted > 0) {
            audit('Delegate permission changed', [
                'role_id' => $role->id,
                'action' => 'revoked',
            ]);
        }
    }

    public function grantDelegate(Account|Role $delegate, Role $role): void
    {
        $permissionName = $this->delegatePermissionName($role);

        if (! $delegate->permissions()->where('name', $permissionName)->exists()) {
            $delegate->givePermissionTo($permissionName);

            audit('Delegate permission changed', [
                $delegate instanceof Account ? 'account_id' : 'delegate_role_id' => $delegate->id,
                'role_id' => $role->id,
                'action' => 'granted',
            ]);
        }
    }

    public function revokeDelegate(Account|Role $delegate, Role $role): void
    {
        $permissionName = $this->delegatePermissionName($role);

        if ($delegate->permissions()->where('name', $permissionName)->exists()) {
            $delegate->revokePermissionTo($permissionName);

            audit('Delegate permission changed', [
                $delegate instanceof Account ? 'account_id' : 'delegate_role_id' => $delegate->id,
                'role_id' => $role->id,
                'action' => 'revoked',
            ]);
        }
    }

    public function getPotentialDelegates(string $search): array
    {
        return Account::query()
            ->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(name_first, ' ', name_last) LIKE ?", ["%{$search}%"]);
            })
            ->limit(50)
            ->get()
            ->filter(fn ($account) => $account->can('admin.access'))
            ->mapWithKeys(fn ($account) => [$account->id => "{$account->name} ({$account->id})"])
            ->toArray();
    }

    public function getPotentialDelegateRoles(string $search, Role $role): array
    {
        return Role::query()
            ->whereKeyNot($role->getKey())
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(50)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getDelegates(Role $role)
    {
        return $this->filterDelegates(Account::query(), $role);
    }

    public function filterDelegates(Builder $query, Role $role): Builder
    {
        $permissionName = $this->delegatePermissionName($role);

        return $query->whereHas('permissions', function ($q) use ($permissionName) {
            $q->where('name', $permissionName);
        });
    }

    public function getRoleDelegates(Role $role): Builder
    {
        return $this->filterRoleDelegates(Role::query(), $role);
    }

    public function filterRoleDelegates(Builder $query, Role $role): Builder
    {
        $permissionName = $this->delegatePermissionName($role);

        return $query->whereHas('permissions', function ($permissionQuery) use ($permissionName) {
            $permissionQuery->where('name', $permissionName);
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
