<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Training\WaitingList;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListPolicy extends BasePolicy
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

    /**
     * Nova Specific Policies
     */
    public function viewAny(Account $account)
    {
        // TODO: Implement viewAny() method.
    }

    public function view(Account $account)
    {
        // TODO: Implement view() method.
    }

    public function create(Account $account)
    {
        // TODO: Implement create() method.
    }

    public function update(Account $account)
    {
        // TODO: Implement update() method.
    }

    public function delete(Account $account)
    {
        // TODO: Implement delete() method.
    }

    public function restore(Account $account)
    {
        // TODO: Implement restore() method.
    }

    public function forceDelete(Account $account)
    {
        // TODO: Implement forceDelete() method.
    }
}
