<?php

namespace App\Policies;

use App\Models\Mship\Account;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Grants all permissions to a "SUPERMAN" user.
     *
     * @return bool
     */
    public function before(Account $account, $policy)
    {
        return $account->roles->contains(Role::findByName('privacc')) ? true : null;
    }
}
