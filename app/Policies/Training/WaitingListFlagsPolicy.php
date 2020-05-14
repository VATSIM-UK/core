<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Policies\BasePolicy;
use Faker\Provider\Base;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListFlagsPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/atc/addFlags", 'web') ||
            $account->checkPermissionTo("waitingLists/pilot/addFlags");
    }
}
