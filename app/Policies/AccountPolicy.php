<?php

namespace App\Policies;

use App\Models\Mship\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Account $actor)
    {
        return $actor->can('adm/mship/account/*');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $actor, Account $subject)
    {
        return $actor->can('adm/mship/account/*');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Account $actor)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $actor, Account $subject)
    {
        return $actor->can('adm/mship/account/*');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $actor, Account $subject)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Account $actor, Account $subject)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Account $actor, Account $subject)
    {
        return false;
    }

    /** Determine whether the user can impersonate the subject account */
    public function impersonate(Account $actor, Account $subject)
    {
        return $actor->can('adm/mship/account/*/impersonate');
    }
}
