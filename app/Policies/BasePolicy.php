<?php

namespace App\Policies;

use App\Models\Model;
use App\Models\Mship\Role;
use App\Models\Mship\Account;
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

    abstract function viewAny(Account $account);

    abstract function view(Account $account);

    abstract function create(Account $account);

    abstract function update(Account $account);

    abstract function delete(Account $account);

    abstract function restore(Account $account);

    abstract function forceDelete(Account $account);
}