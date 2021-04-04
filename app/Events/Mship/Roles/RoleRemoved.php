<?php

namespace App\Events\Mship\Roles;

use App\Events\Event;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;

class RoleRemoved extends Event
{
    use SerializesModels;

    public Account $account;

    public Role $role;

    public function __construct(Account $account, Role $role)
    {
        $this->account = $account;
        $this->role = $role;
    }
}
