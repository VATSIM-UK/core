<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Helpdesk\ReconcileHelpdeskStaffRoleMembers;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RoleHelpdeskStaffSettingObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(RoleHelpdeskStaffSetting $setting): void
    {
        $this->reconcileMembers($setting);
    }

    public function deleted(RoleHelpdeskStaffSetting $setting): void
    {
        $this->reconcileMembers($setting);
    }

    private function reconcileMembers(RoleHelpdeskStaffSetting $setting): void
    {
        ReconcileHelpdeskStaffRoleMembers::dispatch((int) $setting->role_id);
    }
}
