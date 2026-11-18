<?php

namespace App\Services\Roles;

use App\Models\Mship\Account;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PrivilegeCeiling
{
    /**
     * Whether the actor may grant the permission to another account or role.
     */
    public function canGrantPermission(Account $actor, Permission|string $permission): bool
    {
        $name = $permission instanceof Permission ? $permission->name : $permission;

        return $actor->can($name);
    }

    /**
     * Whether the actor may grant the role, i.e. holds every permission it confers.
     */
    public function canGrantRole(Account $actor, Role $role): bool
    {
        return $this->holdsAll($actor, $role->getAllPermissions());
    }

    /**
     * Whether impersonating the subject would leave the actor with no privilege they
     * do not already hold.
     */
    public function canImpersonate(Account $actor, Account $subject): bool
    {
        return $this->holdsAll($actor, $subject->getAllPermissions());
    }

    /**
     * @return array<int>
     */
    public function grantablePermissionIds(Account $actor): array
    {
        return Permission::query()
            ->get()
            ->filter(fn (Permission $permission): bool => $this->canGrantPermission($actor, $permission))
            ->modelKeys();
    }

    /**
     * @return array<int>
     */
    public function grantableRoleIds(Account $actor): array
    {
        return Role::query()
            ->with('permissions')
            ->get()
            ->filter(fn (Role $role): bool => $this->canGrantRole($actor, $role))
            ->modelKeys();
    }

    protected function holdsAll(Account $actor, Collection $permissions): bool
    {
        return $permissions->every(
            fn (Permission $permission): bool => $this->canGrantPermission($actor, $permission)
        );
    }
}
