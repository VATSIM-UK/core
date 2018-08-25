<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Grants all permissions to a "SUPERMAN" user.
     *
     * @param Account $account
     * @param $policy
     * @return bool
     */
    public function before(Account $account, $policy)
    {
        return $account->roles->contains(Role::find(1));
    }

    abstract public function viewAny(Account $account);

    abstract public function view(Account $account);

    abstract public function create(Account $account);

    abstract public function update(Account $account);

    abstract public function delete(Account $account);

    abstract public function restore(Account $account);

    abstract public function forceDelete(Account $account);
}
