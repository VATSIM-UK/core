<?php

namespace App\Models\Mship\Account;

use App\Models\Sys\Token;
use App\Models\Sso\Email as SSOEmail;
use App\Notifications\Mship\EmailVerification;

/**
 * App\Models\Mship\Account\Email
 *
 * @property int $id
 * @property string $email
 * @property int $account_id
 * @property \Carbon\Carbon $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read mixed $is_verified
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Email[] $ssoEmails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Token[] $tokens
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email emailMatches($email)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email verified()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereVerifiedAt($value)
 * @mixin \Eloquent
 */
class Email extends \Eloquent
{
    protected $table = 'mship_account_email';
    protected $dates = ['verified_at', 'created_at', 'updated_at'];
    protected $fillable = ['email'];
    protected $touches = ['account'];

    public function scopeEmailMatches($query, $email)
    {
        return $query->where('email', 'LIKE', sanitize_email($email));
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }

    public function tokens()
    {
        return $this->morphMany(\App\Models\Sys\Token::class, 'related');
    }

    public function ssoEmails()
    {
        return $this->hasMany(\App\Models\Sso\Email::class, 'account_email_id');
    }

    public function assignToSso($ssoAccount)
    {
        // Let's just check it's not already assigned.
        $alreadyAssigned = $this->ssoEmails->filter(function ($email) use ($ssoAccount) {
            return $email->sso_account_id == $ssoAccount->id;
        });

        if ($alreadyAssigned && count($alreadyAssigned) > 0) {
            return true;
        }

        $ssoEmail = new SSOEmail;
        $ssoEmail->account_id = $this->account->id;
        $ssoEmail->account_email_id = $this->getKey();
        $ssoEmail->sso_account_id = $ssoAccount->getKey();
        $ssoEmail->save();

        return true;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = sanitize_email($value);
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verified_at != null;
    }

    public function __toString()
    {
        return isset($this->attributes['email']) ? $this->attributes['email'] : '';
    }

    /**
     * Save the email's current state.
     *
     * If the email isn't verified, a verification email will be dispatched.
     *
     * @param array $options Additional options to use when saving this Email.
     * @return bool
     */
    public function save(array $options = [])
    {
        $saveResult = parent::save($options);

        if (!$this->is_verified) {
            $generatedToken = Token::generate('mship_account_email_verify', false, $this);

            $this->account->notify(new EmailVerification($this, $generatedToken));
        }

        return $saveResult;
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($email) {
            // Cause the delete of a secondary email to cascade to its assignments
            $email->ssoEmails()->delete();
        });
    }
}
