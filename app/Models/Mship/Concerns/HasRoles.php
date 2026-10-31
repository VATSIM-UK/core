<?php

namespace App\Models\Mship\Concerns;

use App\Events\Mship\Roles\RoleAssigned;
use App\Events\Mship\Roles\RoleRemoved;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles as OriginalHasRoles;

/**
 * @mixin \App\Models\BaseModel
 */
trait HasRoles
{
    use OriginalHasRoles {
        assignRole as protected originalAssignRole;
        removeRole as protected originalRemoveRole;
        syncRoles as protected originalSyncRoles;
    }

    /**
     * @param  mixed  ...$roles
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $this->originalAssignRole(...$roles);

        $this->fireRoleAssignedEvent($roles);

        return $this;
    }

    /**
     * @return bool
     */
    public function fireRoleAssignedEvent($role)
    {
        if (is_iterable($role)) {
            return array_walk($role, [$this, 'fireRoleAssignedEvent']);
        }

        event(new RoleAssigned($this, $this->getStoredRole($role)));

        return true;
    }

    /**
     * @return $this
     */
    public function removeRole($role)
    {
        $this->originalRemoveRole($role);

        $this->fireRoleRemovedEvent($role);

        return $this;
    }

    /**
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $before = $this->roles()->pluck('mship_role.id');

        $this->originalSyncRoles(...$roles);

        $after = $this->roles()->pluck('mship_role.id');

        Role::query()
            ->whereIn('id', $after->diff($before))
            ->each(fn (Role $role) => $this->fireRoleAssignedEvent($role));

        Role::query()
            ->whereIn('id', $before->diff($after))
            ->each(fn (Role $role) => $this->fireRoleRemovedEvent($role));

        return $this;
    }

    /**
     * @return bool
     */
    public function fireRoleRemovedEvent($role)
    {
        if (is_iterable($role)) {
            return array_walk($role, [$this, 'fireRoleRemovedEvent']);
        }

        event(new RoleRemoved($this, $this->getStoredRole($role)));

        return true;
    }
}
