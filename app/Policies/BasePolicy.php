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
     * @param Account $account
     * @param $policy
     * @return bool
     */
    public function before(Account $account, $policy)
    {
        return $account->roles->contains(Role::findByName('privacc')) ? true : null;
    }

    abstract public function viewAny(Account $account);

    abstract public function view(Account $account);

    abstract public function create(Account $account);

    abstract public function update(Account $account);

    abstract public function delete(Account $account);

    abstract public function restore(Account $account);

    abstract public function forceDelete(Account $account);
}
