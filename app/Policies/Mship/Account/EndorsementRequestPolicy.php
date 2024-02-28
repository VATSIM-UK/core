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
        return $account->hasAnyPermission('endorsement-request.access');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return $account->hasAnyPermission("endorsement-request.view.{$endorsementRequest->type}");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return $account->hasAnyPermission('endorsement-request.create.*');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return false;
    }

    public function approve(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return $account->hasAnyPermission("endorsement-request.approve.{$endorsementRequest->type}");
    }

    public function reject(Account $account, EndorsementRequest $endorsementRequest): bool
    {
        return $account->hasAnyPermission("endorsement-request.reject.{$endorsementRequest->type}");
    }
}
