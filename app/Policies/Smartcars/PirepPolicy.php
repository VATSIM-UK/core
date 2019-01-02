<?php

namespace App\Policies\Smartcars;

use App\Models\Mship\Account;
use App\Models\Smartcars\Pirep;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class PirepPolicy
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
        return $account->roles->contains(Role::findByName('privacc'));
    }

    /**
     * Determine whether the user can view the pirep.
     *
     * @param  Account $account
     * @param  Pirep $pirep
     * @return mixed
     */
    public function view(Account $account, Pirep $pirep)
    {
        return $pirep->bid->account->id == $account->id;
    }

    public function viewAny(Account $account)
    {
        // TODO: Implement viewAny() method.
    }

    public function create(Account $account)
    {
        // TODO: Implement create() method.
    }

    public function update(Account $account)
    {
        // TODO: Implement update() method.
    }

    public function delete(Account $account)
    {
        // TODO: Implement delete() method.
    }

    public function restore(Account $account)
    {
        // TODO: Implement restore() method.
    }

    public function forceDelete(Account $account)
    {
        // TODO: Implement forceDelete() method.
    }
}
