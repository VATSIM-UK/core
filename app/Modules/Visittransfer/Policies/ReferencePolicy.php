<?php

namespace App\Modules\Visittransfer\Policies;

use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Reference;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReferencePolicy
{
    use HandlesAuthorization;

    public function complete(Account $user, Reference $reference)
    {
        return $reference->account_id == $user->id && $reference->is_requested;
    }

    public function reject(Account $user, Reference $reference)
    {
        return true;
        // TODO: Figure out these permissions.
    }

    public function accept(Account $user, Reference $reference)
    {
        return true;
        // TODO: Figure out these permissions.
    }
}
