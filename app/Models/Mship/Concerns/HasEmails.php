<?php

namespace App\Models\Mship\Concerns;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\Email as AccountEmail;
use Carbon\Carbon;

trait HasEmails
{
    /**
     * Fetch all related secondary emails.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function secondaryEmails()
    {
        return $this->hasMany(\App\Models\Mship\Account\Email::class, 'account_id');
    }

    public function ssoEmails()
    {
        return $this->hasManyThrough(\App\Models\Sso\Email::class, Email::class, 'account_id', 'account_email_id');
    }

    /**
     * Determine if the current account has the given email attached to it.
     *
     * @param  string  $email  The email to check is attached to this account.
     * @param  bool  $checkPrimary  Whether to also check the primary email address.
     * @return bool
     */
    public function hasEmail($email, $checkPrimary = true)
    {
        if ($checkPrimary && strcasecmp($email, $this->email) == 0) {
            return true;
        }

        $check = $this->secondaryEmails->filter(function ($e) use ($email) {
            return strcasecmp($e->email, $email) == 0;
        })->count();

        return $check > 0;
    }

    /**
     * Set an account's primary email to the one given.
     *
     * If the primary email exists as a secondary, it'll be deleted.
     *
     * @param  string  $primaryEmail  The new primary email for the account.
     * @return bool
     */
    public function setEmail($primaryEmail)
    {
        $checkPrimaryEmail = false;
        if ($this->hasEmail($primaryEmail, $checkPrimaryEmail)) {
            $secondaryEmail = $this->secondaryEmails->filter(function ($secondaryEmail) use ($primaryEmail) {
                return strcasecmp($secondaryEmail->email, $primaryEmail) == 0;
            })->first();

            $secondaryEmail->delete();
        }

        $this->attributes['email'] = strtolower($primaryEmail);
        $save = $this->save();

        if ($this->email != strtolower($primaryEmail)) {
            event(new AccountAltered($this));
        }

        return $save;
    }

    /**
     * Retrieve an email address for a given SSO service.
     *
     * @param  $sso_account_id
     * @return string
     */
    public function getEmailForService($ssoAccountId)
    {
        $emailForService = $this->ssoEmails()->where('sso_account_id', $ssoAccountId)->with('email')->first();

        return $emailForService ? $emailForService->email->email : $this->email;
    }

    /**
     * Laravel magic setter - calls the setEmail method and instantly saves.
     *
     * @param  string  $email
     * @return bool
     */
    public function setEmailAttribute($email)
    {
        return $this->setEmail($email);
    }

    /**
     * Attach a new secondary email to this user account.
     *
     * @param  string  $newEmail  The new email address to add to this account.
     * @param  bool  $verified  Set to TRUE if the email should be automatically verified.
     * @return \Illuminate\Database\Eloquent\Model|Email|false
     */
    public function addSecondaryEmail($newEmail, $verified = false)
    {
        if (! $this->hasEmail($newEmail)) {
            $newSecondaryEmail = new AccountEmail(['email' => $newEmail]);
            $newSecondaryEmail->verified_at = ($verified ? Carbon::now() : null);

            $save = $this->secondaryEmails()->save($newSecondaryEmail);

            if ($verified) {
                event(new AccountAltered($this));
            }

            return $save;
        }

        return $this->secondaryEmails->filter(function ($e) use ($newEmail) {
            return strcasecmp($e->email, $newEmail) == 0;
        })->first();
    }

    /**
     * Filter the attached secondary emails for those that are verified.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVerifiedSecondaryEmailsAttribute()
    {
        if ($this->secondaryEmails->isEmpty()) {
            return collect();
        }

        return $this->secondaryEmails->filter(function ($email) {
            return $email->is_verified;
        });
    }
}
