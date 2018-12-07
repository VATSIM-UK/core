<?php

namespace App\Policies;

use App\Models\Community\Membership;
use App\Models\Mship\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipPolicy
{
    public function deploy(Account $user)
    {
        return $user->communityGroups()->notDefault()->count() == 0 && $user->hasState('DIVISION');
    }
}
