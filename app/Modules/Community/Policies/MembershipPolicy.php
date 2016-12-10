<?php

namespace App\Modules\Community\Policies;

use App\Models\Mship\Account;
use App\Modules\Community\Models\Membership;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipPolicy
{
    use HandlesAuthorization;

    public function deploy(Account $user, Membership $membership)
    {
        return $user->communityGroups()->count() == 0;
    }
}
