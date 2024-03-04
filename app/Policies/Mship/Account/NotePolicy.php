<?php

namespace App\Policies\Mship\Account;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Note;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->hasPermissionTo('account.view-insensitive.*');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, Note $note): bool
    {
        return $account->hasPermissionTo("account.view-insensitive.{$note->account_id}");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return $account->hasPermissionTo('account.note.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, Note $note): bool
    {
        return false;
    }
}
