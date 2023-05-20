<?php

namespace App\Policies\Smartcars;

use App\Models\Mship\Account;
use App\Models\Smartcars\Flight;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExercisePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create a bid for the flight.
     *
     * @return mixed
     */
    public function bid(Account $account, Flight $flight)
    {
        return $flight->enabled;
    }
}
