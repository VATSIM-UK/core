<?php

namespace App\Models\Mship\Account;

use App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess;
use App\Models\Sso\Email as SSOEmail;
use Validator;

/**
 * App\Models\Mship\Account\Email
 *
 * @property integer $account_email_id
 * @property integer $account_id
 * @property string $email
 * @property boolean $is_primary
 * @property string $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Token[] $tokens
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Email[] $ssoEmails
 * @property-read mixed $is_verified
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email primary()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email secondary()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email verified()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email emailMatches($email)
 * @property integer $id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereVerifiedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Email whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Email extends \Eloquent
{

    protected $table      = "mship_account_email";
    protected $dates      = ['verified_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable   = ['email'];
    protected $touches    = ['account'];

    public function scopeEmailMatches($query, $email){
        return $query->where("email", "LIKE", sanitize_email($email));
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull("verified_at");
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id");
    }

    public function tokens()
    {
        return $this->morphMany(\App\Models\Sys\Token::class, "related");
    }

    public function ssoEmails()
    {
        return $this->hasMany(\App\Models\Sso\Email::class, "account_email_id");
    }

    public function assignToSso($ssoAccount)
    {
        // Let's just check it's not already assigned.
        $alreadyAssigned = $this->ssoEmails->filter(function ($email) use ($ssoAccount) {
            return $email->sso_account_id == $ssoAccount->sso_account_id;
        }
        );

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
        return ($this->attributes['verified_at'] != null);
    }

    public function getIsPrimaryAttribute()
    {
        return ($this->attributes['is_primary'] == 1);
    }

    public function __toString()
    {
        return isset($this->attributes['email']) ? $this->attributes['email'] : "";
    }

    /**
     * Save the email's current state.
     *
     * If the email isn't verified, a verification email will be dispatched.
     *
     * @param array $options Additional options to use when saving this Email.
     * @return boolean
     */
    public function save(array $options=[]){
        $saveResult = parent::save($options);

        if(!$this->is_verified){
            dispatch(new TriggerNewEmailVerificationProcess($this));
        }

        return $saveResult;
    }
}
