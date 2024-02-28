<?php

namespace App\Policies;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;

class PositionGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->hasAnyPermission('position-group.view.*');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, PositionGroup $positionGroup): bool
    {
        return $account->hasAnyPermission('position-group.view.*');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, PositionGroup $positionGroup): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, PositionGroup $positionGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, PositionGroup $positionGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, PositionGroup $positionGroup): bool
    {
        return false;
    }

    public function endorse(Account $account): bool
    {
        return $account->hasAnyPermission('endorsement.create.*');
    }
}
