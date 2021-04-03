<?php

namespace App\Events\Mship\Roles;

use App\Events\Event;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use Illuminate\Queue\SerializesModels;

class RoleAssigned extends Event
{
    use SerializesModels;

    /* @var Account */
    public $account;

    /* @var Role */
    public $role;

    public function __construct(Account $account, Role $role)
    {
        $this->account = $account;
        $this->role = $role;
    }
}
