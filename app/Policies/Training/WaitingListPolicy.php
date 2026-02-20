<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class WaitingListPolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $account)
    {
        return $account->hasAnyPermission('waiting-lists.access');
    }

    public function view(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.view.%s']);
    }

    public function addAccounts(Account $account, WaitingList $waitingList)
    {
        return ! $waitingList->isAtCapacity() &&
            $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.add-accounts.%s']);
    }

    public function addAccountsAdmin(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.add-accounts-admin.%s']);
    }

    public function updateAccounts(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.update-accounts.%s']);
    }

    public function removeAccount(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.remove-accounts.%s']);
    }

    public function addFlags(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.add-flags.%s']);
    }

    public function update(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.admin.%s']);
    }

    public function delete(Account $account, WaitingList $waitingList)
    {
        return $this->checkHasPermissionForList($account, $waitingList, ['waiting-lists.delete.%s']);
    }

    public function create(Account $account)
    {
        return $account->hasAnyPermission(['waiting-lists.create']);
    }

    public function selfEnrol(Account $account, WaitingList $waitingList)
    {
        return WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList);
    }

    public function trainingPlacesManualSetup(Account $account, WaitingList $waitingList)
    {
        return $account->hasAnyPermission('training-places.manual-setup');
    }

    /**
     * Returns if the account has permission for either the ID or department type for the given permission
     *
     * @param  string|string[]  $permissionTemplate  Of sprintf format, e.g. "permsion.to.%s"
     * @return void
     */
    private function checkHasPermissionForList(Account $account, WaitingList $waitingList, mixed $permissionTemplates): bool
    {
        return $account->hasAnyPermission(collect(Arr::wrap($permissionTemplates))->flatMap(fn ($permissionTemplate) => [sprintf($permissionTemplate, $waitingList->id), sprintf($permissionTemplate, $waitingList->department)]));
    }
}
