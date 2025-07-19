<?php

namespace App\Policies\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListRetentionChecksPolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $user): bool {}

    public function view(Account $user, WaitingListRetentionChecks $waitingListRetentionChecks): bool {}

    public function create(Account $user): bool {}

    public function update(Account $user, WaitingListRetentionChecks $waitingListRetentionChecks): bool {}

    public function delete(Account $user, WaitingListRetentionChecks $waitingListRetentionChecks): bool {}

    public function restore(Account $user, WaitingListRetentionChecks $waitingListRetentionChecks): bool {}

    public function forceDelete(Account $user, WaitingListRetentionChecks $waitingListRetentionChecks): bool {}
}
