<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Mship\ReconcileHelpdeskStaff;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    public function deleting(Role $role): void
    {
        if (! RoleHelpdeskStaffSetting::query()->where('role_id', $role->getKey())->exists()) {
            return;
        }

        $role->users()
            ->pluck('mship_account.id')
            ->each(fn ($accountId) => ReconcileHelpdeskStaff::dispatch((int) $accountId)->afterCommit());
    }
}
