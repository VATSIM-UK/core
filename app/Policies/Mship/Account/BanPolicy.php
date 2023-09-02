<?php

namespace App\Policies\Mship\Account;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Auth\Access\HandlesAuthorization;

class BanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Account $account)
    {
        return $account->canAny(['account.ban.create', 'account.ban.edit.*', 'account.ban.repeal.*', 'account.view-sensitive.*']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Ban  $ban
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Ban $ban)
    {
        return $this->viewAny($account);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Account $account, Account $subject = null)
    {
        return $account->can('account.ban.create') && ! $subject?->is_banned;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Ban $ban)
    {
        return $account->can("account.ban.edit.{$ban->id}");
    }

    /**
     * Determine whether the user can repeal the van.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function repeal(Account $account, Ban $ban)
    {
        return $ban->is_active && $account->can("account.ban.repeal.{$ban->id}");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Ban $ban)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Account $account, Ban $ban)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Account $account, Ban $ban)
    {
        return false;
    }
}
