<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;

class EndorsementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, Endorsement $endorsement): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return $account->hasAnyPermission('endorsement.create.*');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, Endorsement $endorsement): bool
    {
        return $account->hasAnyPermission('endorsement.temporary.edit.*');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, Endorsement $endorsement): bool
    {
        return $account->hasAnyPermission('endorsement.create.*');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, Endorsement $endorsement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, Endorsement $endorsement): bool
    {
        return false;
    }
}
