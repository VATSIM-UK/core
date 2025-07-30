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
        return $actor->canAny(['account.view-insensitive.*', 'account.view-sensitive.*']);
    }

    public function syncDiscord(Account $actor)
    {
        return $actor->canAny(['account.view-insensitive.*']);
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
        return $actor->canAny(['account.view-insensitive.*', 'account.view-sensitive.*']) && ($subject->getKey() !== $actor->getKey() || $actor->can('account.self'));
    }

    /**
     * Determine whether the user can view sensitive information on the model.
     *
     * @param  \App\Models\Mship\Account  $account
     * @param  \App\Models\Mship\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSensitive(Account $actor, Account $subject)
    {
        return $actor->can('account.view-sensitive.*');
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
        return $actor->can("account.edit-basic-details.{$subject->id}") && $this->passesSelfCheck($actor, $subject);
    }

    /**
     * Whether the user can remove another user's secondary password
     *
     * @return void
     */
    public function removeSecondaryPassword(Account $actor, Account $subject)
    {
        return $actor->can("account.remove-password.{$subject->id}") && $this->passesSelfCheck($actor, $subject);
    }

    /**
     * Whether the user can unlink another user's Discount account
     *
     * @return void
     */
    public function unlinkDiscordAccount(Account $actor, Account $subject)
    {
        return $actor->can("account.unlink-discord.{$subject->id}");
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
        return $actor->can("account.impersonate.{$subject->id}");
    }

    protected function passesSelfCheck(Account $actor, Account $subject)
    {
        return $subject->getKey() !== $actor->getKey() || $actor->can('account.self');
    }
}
