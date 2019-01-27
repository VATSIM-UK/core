<?php

namespace App\Policies;

use App\Models\Mship\Account;

class MembershipPolicy
{
    public function deploy(Account $user)
    {
        return $user->communityGroups()->notDefault()->count() == 0 && $user->hasState('DIVISION');
    }
}
