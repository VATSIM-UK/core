<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Training\WaitingList;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListPolicy
{
    use HandlesAuthorization;

    /**
     * Allow SuperAdmins all permissions.
     *
     * @param Account $account
     * @param $policy
     * @return mixed
     */
    public function before(Account $account, $policy)
    {
        return $account->roles->contains(Role::find(1));
    }

    public function addAccount(Account $account, WaitingList $waitingList)
    {
        return $this->basePermission($account, $waitingList);
    }

    public function removeAccount(Account $account, WaitingList $waitingList)
    {
        return $this->basePermission($account, $waitingList);
    }

    public function promoteAccount(Account $account, WaitingList $waitingList)
    {
        return $this->basePermission($account, $waitingList);
    }

    public function demoteAccount(Account $account, WaitingList $waitingList)
    {
        return $this->basePermission($account, $waitingList);
    }

    private function basePermission(Account $account, WaitingList $waitingList)
    {
        return $waitingList->staff->contains($account);
    }
}
