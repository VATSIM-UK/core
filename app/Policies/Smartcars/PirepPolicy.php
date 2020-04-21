<?php

namespace App\Policies\Smartcars;

use App\Models\Mship\Account;
use App\Models\Smartcars\Pirep;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class PirepPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the pirep.
     *
     * @param  Account $account
     * @param  Pirep $pirep
     * @return bool
     */
    public function viewAccount(Account $account, Pirep $pirep)
    {
        return $pirep->bid->account->id == $account->id;
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

    public function view(Account $account)
    {
        return null;
    }

    public function viewAny(Account $account)
    {
        return null;
    }
}
