<?php

namespace App\Policies;

use App\Models\Community\Membership;
use App\Models\Mship\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipPolicy
{
    use HandlesAuthorization;

    public function deploy(Account $user, Membership $membership)
    {
        return $user->communityGroups()->notDefault()->count() == 0;
    }
}
