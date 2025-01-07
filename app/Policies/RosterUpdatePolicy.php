<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\RosterUpdate;
use Illuminate\Auth\Access\Response;

class RosterUpdatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->can('roster.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, RosterUpdate $rosterUpdate): bool
    {
        return $account->can('roster.manage');
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
    public function update(Account $account, RosterUpdate $rosterUpdate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, RosterUpdate $rosterUpdate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, RosterUpdate $rosterUpdate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, RosterUpdate $rosterUpdate): bool
    {
        return false;
    }
}
