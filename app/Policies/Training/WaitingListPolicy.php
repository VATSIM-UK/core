<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaitingListPolicy extends BasePolicy
{
    use HandlesAuthorization;

    private const GUARD = 'web';

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
            || $account->hasPermissionTo("waitingLists/{$waitingList->department}/{$waitingList->slug}/accounts/add", self::GUARD);
    }

    public function removeAccount(Account $account, WaitingList $waitingList)
    {
        return true;
    }

    public function elevatedInformation(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList)
            || $account->checkPermissionTo('waitingLists/elevatedInformation', self::GUARD);
    }

    public function addFlags(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList)
            || $account->checkPermissionTo("waitingLists/{$waitingList->department}/flags/add", self::GUARD);
    }

    private function departmentWildcard(Account $account, string $department)
    {
        return $account->checkPermissionTo("waitingLists/{$department}/*", self::GUARD);
    }

    /**
     * Can view any waiting list resources.
     * @param Account $account
     * @return bool
     */
    public function viewAny(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/atc/base", self::GUARD)
            || $account->checkPermissionTo("waitingLists/pilot/base", self::GUARD);
    }

    public function view(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList->department)
            || $account->checkPermissionTo("waitingLists/{$waitingList->department}/{$waitingList->slug}/view", self::GUARD);
    }

    public function create(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/create", self::GUARD);
    }

    public function update(Account $account, WaitingList $waitingList)
    {
        return $this->departmentWildcard($account, $waitingList->department)
            || $account->getPermissionsViaRoles()->contains("waitingLists/{$waitingList->department}/{$waitingList->slug}/edit", self::GUARD);
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
