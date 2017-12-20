<?php

namespace App\Policies\Smartcars;

use App\Models\Mship\Account;
use App\Models\Smartcars\Pirep;
use Illuminate\Auth\Access\HandlesAuthorization;

class PirepPolicy
{
    use HandlesAuthorization;

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
}
