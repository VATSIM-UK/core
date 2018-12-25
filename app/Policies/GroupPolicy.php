<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\Community\Group;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    public function deploy(Account $user, Group $group)
    {
        return ! $group->hasMember($user) && $user->hasState('DIVISION');
    }
}
