<?php

namespace App\Policies;

use App\Models\Mship\Account;

class PasswordPolicy extends Policy
{
    /**
     * Determine whether the user can create a new password.
     *
     * @return mixed
     */
    public function create(Account $user)
    {
        if ($user->hasPassword()) {
            return $this->deny('You already have a password set.');
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can update their password.
     *
     * @return mixed
     */
    public function change(Account $user)
    {
        if (! $user->hasPassword()) {
            return $this->deny('You do not have a password set.');
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can delete their password.
     *
     * @return mixed
     */
    public function delete(Account $user)
    {
        if ($user->mandatory_password) {
            return $this->deny('You are not allowed to disable your secondary password.');
        } elseif (! $user->hasPassword()) {
            return $this->deny('You do not have a password set.');
        }

        return $this->allow();
    }
}
