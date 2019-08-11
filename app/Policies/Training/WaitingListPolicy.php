<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function before(Account $account, $policy)
    {
        if (parent::before($account, $policy)) {
            return true;
        }
        if ($account->checkPermissionTo('waitingLists/*')) {
            return true;
        }
        return null;
    }

    public function addAccounts(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList)
            || $account->hasPermissionTo("waitingLists/{$waitingList->department}/{$waitingList->slug}/accounts/add");
    }

    public function removeAccount(Account $account, WaitingList $waitingList)
    {
        return true;
    }

    public function elevatedInformation(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList)
            || $account->checkPermissionTo('waitingLists/elevatedInformation');
    }

    public function addFlags(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList)
            || $account->checkPermissionTo("waitingLists/{$waitingList->department}/flags/add");
    }

    private function departmentWildcard(Account $account, string $department)
    {
        return $account->checkPermissionTo("waitingLists/{$department}/*");
    }

    /**
     * Can view any waiting list resources.
     * @param Account $account
     * @return bool
     */
    public function viewAny(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/base", "web");
    }

    public function view(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList->department)
            || $account->checkPermissionTo("waitingLists/{$waitingList->department}/{$waitingList->slug}/view");
    }

    public function create(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/create");
    }

    public function update(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList->department)
            || $account->getPermissionsViaRoles()->contains("waitingLists/{$waitingList->department}/{$waitingList->slug}/edit");
    }

    public function delete(Account $account, WaitingList  $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList->department);
    }

    public function restore(Account $account)
    {
        return false;
    }

    public function forceDelete(Account $account)
    {
        return false;
    }
}
