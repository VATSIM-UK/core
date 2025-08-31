<?php

namespace App\Policies\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListRetentionChecksPolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $user): bool
    {
        return $user->hasAnyPermission('waiting-lists.view.*');
    }

    public function view(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool
    {
        return $user->can("waiting-lists.view.$waitingListRetentionCheck->waitingListAccount->waitingList->department");
    }

    public function create(Account $user): bool
    {
        return false;
    }

    public function update(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool
    {
        return false;
    }

    public function delete(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool
    {
        return false;
    }

    public function restore(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool
    {
        return false;
    }

    public function forceDelete(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool
    {
        return false;
    }
}
