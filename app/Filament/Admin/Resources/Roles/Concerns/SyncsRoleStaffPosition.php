<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Roles\Concerns;

use App\Models\Mship\RoleStaffPosition;
use Illuminate\Contracts\Support\Arrayable;

trait SyncsRoleStaffPosition
{
    protected function syncRoleStaffPosition(): void
    {
        $state = $this->form->getRawState();

        RoleStaffPosition::syncForRole(
            $this->getRecord(),
            $state instanceof Arrayable ? $state->toArray() : $state,
        );
    }
}
