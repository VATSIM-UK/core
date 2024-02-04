<?php

namespace App\Policies\Mship\Account;

use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;

class EndorsementRequestPolicy
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
    public function view(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return true;
    }
}
