<?php

declare(strict_types=1);

namespace App\Listeners\Mship;

use App\Events\Mship\Roles\RoleAssigned;
use App\Events\Mship\Roles\RoleRemoved;
use App\Jobs\Mship\ReconcileHelpdeskStaff;
use App\Models\Mship\RoleHelpdeskStaffSetting;

class ReconcileHelpdeskStaffOnRoleChange
{
    public function handle(RoleAssigned|RoleRemoved $event): void
    {
        if (! RoleHelpdeskStaffSetting::query()->where('role_id', $event->role->getKey())->exists()) {
            return;
        }

        ReconcileHelpdeskStaff::dispatch((int) $event->account->getKey())->afterCommit();
    }
}
