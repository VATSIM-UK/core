<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Roles\Concerns;

use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Contracts\Support\Arrayable;

trait SyncsRoleHelpdeskStaffSetting
{
    protected function syncRoleHelpdeskStaffSetting(): void
    {
        $state = $this->form->getRawState();

        RoleHelpdeskStaffSetting::syncForRole(
            $this->getRecord(),
            $state instanceof Arrayable ? $state->toArray() : $state,
        );
    }
}
