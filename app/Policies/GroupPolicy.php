<?php

namespace App\Policies;

use App\Models\Community\Group;
use App\Models\Mship\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    public function deploy(Account $user, Group $group)
    {
        return ! $group->hasMember($user) && $user->hasState('DIVISION') && $user->communityGroups()->where('tier', $group->tier)->count() == 0;
    }
}
