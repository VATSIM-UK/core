<?php

namespace App\Models\Sso;

use App\Events\Mship\AccountAltered;
use App\Models\Model;

/**
 * App\Models\Sso\Email.
 *
 * @property int $id
 * @property int|null $account_email_id
 * @property int $sso_account_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\Mship\Account\Email|null $email
 * @property-read \Laravel\Passport\Client $ssoAccount
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sso\Email whereAccountEmailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sso\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sso\Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sso\Email whereSsoAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sso\Email whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Email extends Model
{
    protected $table = 'mship_oauth_emails';

    protected $primaryKey = 'id';

    protected $dates = ['created_at', 'updated_at'];

    protected $hidden = ['id'];

    public function email()
    {
        return $this->belongsTo(\App\Models\Mship\Account\Email::class, 'account_email_id', 'id');
    }

    public function ssoAccount()
    {
        return $this->belongsTo(\Laravel\Passport\Client::class, 'sso_account_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($email) {
            event(new AccountAltered($email->email->account));
        });
    }
}
