<?php

namespace App\Models\Mship\Concerns;

use App\Notifications\Mship\ForgottenPasswordLink;
use Auth;
use Carbon\Carbon;
use Hash;
use Session;

trait HasPassword
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ForgottenPasswordLink($token));
    }

    public function verifyPassword($password)
    {
        if ($this->password == sha1(sha1($password))) {
            $this->password = $password;
            $this->save();
        }

        return Hash::check($password, $this->password);
    }

    /**
     * Set the password attribute correctly.
     *
     * Will hash the password, or set it as null if required.
     *
     * @param  null|string  $password  The password value to set.
     */
    public function setPasswordAttribute($password)
    {
        // if password is null, remove the current password
        // elseif password is already hashed, store it as provided
        // else password needs hashing, hash and store it
        if ($password === null) {
            $this->attributes['password'] = null;
        } elseif (! Hash::needsRehash($password)) {
            $this->attributes['password'] = $password;
        } else {
            $this->attributes['password'] = Hash::make($password);
        }
    }

    /**
     * Determine whether the current account has a password set.
     *
     * @return bool
     */
    public function hasPassword()
    {
        return $this->password !== null;
    }

    /**
     * Determine whether the current password has expired.
     *
     * @return bool
     */
    public function hasPasswordExpired()
    {
        if (! $this->hasPassword()) {
            return false;
        }

        if ($this->password_expires_at === null) {
            return false;
        }

        return $this->password_expires_at->lte(now());
    }

    /**
     * Get password lifetime attribute from the member's roles.
     *
     * @return int
     */
    public function getPasswordLifetimeAttribute()
    {
        return $this->roles()
            ->orderBy('password_lifetime', 'DESC')
            ->first()
            ->password_lifetime;
    }

    /**
     * Determine whether this account's password is mandatory.
     *
     * @return bool
     */
    public function getMandatoryPasswordAttribute()
    {
        return $this->roles()
            ->get()
            ->filter(function ($value) {
                return $value->password_mandatory;
            })
            ->isNotEmpty();
    }

    /**
     * Calculate the password expiry for this account.
     *
     * @param  bool  $temporary  Should we treat the password as temporary?
     * @return null|Carbon
     */
    public function calculatePasswordExpiry($temporary = false)
    {
        if ($temporary) {
            return Carbon::now();
        }

        if ($this->password_lifetime > 0) {
            return Carbon::now()->addDays($this->password_lifetime);
        }
    }

    /**
     * Set the user's password.
     *
     * @param  string  $password  The password string.
     * @param  bool  $temporary  Will only be a temporary password
     * @return bool
     */
    public function setPassword($password, $temporary = false)
    {
        $save = $this->fill([
            'password' => $password,
            'password_set_at' => Carbon::now(),
            'password_expires_at' => $this->calculatePasswordExpiry($temporary),
        ])->save();

        // if the password is being reset by its owner...
        if ($save && Auth::check() && Auth::user()->id === $this->id) {
            Session::put([
                'password_hash' => Auth::user()->getAuthPassword(),
            ]);
        }

        return $save;
    }

    /**
     * Remove a member's current password.
     *
     * @return bool
     */
    public function removePassword()
    {
        return $this->fill([
            'password' => null,
            'password_set_at' => null,
            'password_expires_at' => null,
        ])->save();
    }
}
