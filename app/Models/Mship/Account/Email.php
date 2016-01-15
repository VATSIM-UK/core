<?php

namespace App\Models\Mship\Account;

use App\Models\Sso\Email as SSOEmail;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
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
 */
class Email extends \Eloquent
{

    use SoftDeletingTrait;

    protected $table      = "mship_account_email";
    protected $primaryKey = "account_email_id";
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable   = ['email'];
    protected $attributes = ['is_primary' => 0];
    protected $touches    = ['account'];

    public function account()
    {
        return $this->hasOne("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function tokens()
    {
        return $this->morphMany("\App\Models\Sys\Token", "related");
    }

    public function scopePrimary($query)
    {
        return $query->where("is_primary", "=", 1);
    }

    public function scopeSecondary($query)
    {
        return $query->where("is_primary", "=", 0);
    }

    public function scopeVerified($query)
    {
        return $query->where("verified_at", ">", "0000-00-00 00:00:00");
    }

    public function ssoEmails()
    {
        return $this->hasMany("\App\Models\Sso\Email", "account_email_id", "account_email_id");
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
        $ssoEmail->account_id = $this->account->account_id;
        $ssoEmail->account_email_id = $this->getKey();
        $ssoEmail->sso_account_id = $ssoAccount->getKey();
        $ssoEmail->save();

        return true;
    }

    public function setEmailAttribute($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $this->attributes['email'] = $value;
    }

    public function getIsVerifiedAttribute()
    {
        return $this->attributes['verified_at'] != null;
    }

    public function getIsPrimaryAttribute()
    {
        return (boolean)$this->attributes['is_primary'];
    }

    public function setIsPrimaryAttribute($value)
    {
        // Are we just setting to false?
        if ($value == 0 OR !$value) {
            $this->attributes['is_primary'] = 0;
            $this->save();

            return true;
        }

        // Next, let's check if this email is already primary.  If it is, no chance.
        if ($this->is_primary) {
            return false;
        }

        // Finally, let's demote other primary emails.
        if ($this->account) {
            foreach ($this->account->emails as $e) {
                $e->is_primary = 0;
                $e->save();
            }
        }

        // Now upgrade this!
        $this->attributes['is_primary'] = 1;
        $this->save();
    }

    public function __toString()
    {
        return isset($this->attributes['email']) ? $this->attributes['email'] : "";
    }
}
