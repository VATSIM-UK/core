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
    }

    public function view(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/view", self::GUARD);
    }

    public function addAccounts(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/addAccounts", self::GUARD);
    }

    public function removeAccount(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/removeAccount", self::GUARD);
    }

    public function elevatedInformation(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/elevatedInformation", self::GUARD);
    }

    public function addFlags(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/addFlags", self::GUARD);
    }

    public function update(Account $account, WaitingList $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/update", self::GUARD);
    }

    public function delete(Account $account, WaitingList  $waitingList)
    {
        return $account->checkPermissionTo("waitingLists/{$waitingList->department}/delete", self::GUARD);
    }

    public function create(Account $account)
    {
        return $account->checkPermissionTo("waitingLists/create", self::GUARD);
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
