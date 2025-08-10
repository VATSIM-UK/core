<?php

namespace App\Policies\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListRetentionChecksPolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $user): bool {}

    public function view(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool {}

    public function create(Account $user): bool {}

    public function update(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool {}

    public function delete(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool {}

    public function restore(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool {}

    public function forceDelete(Account $user, WaitingListRetentionCheck $waitingListRetentionCheck): bool {}
}
