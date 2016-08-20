<?php

namespace App\Models\Sso;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sso\Email
 *
 * @property integer $id
 * @property integer $account_id
 * @property integer $account_email_id
 * @property integer $sso_account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account\Email $email
 * @property-read \App\Models\Sso\Account $ssoAccount
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereAccountEmailId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereSsoAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Email whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Email extends \Eloquent {

    use RecordsActivity;

    protected $table = "sso_email";
    protected $primaryKey = "id";
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['id'];

    public function email(){
        return $this->belongsTo(\App\Models\Mship\Account\Email::class, "account_email_id", "id");
    }

    public function ssoAccount(){
        return $this->belongsTo(\App\Models\Sso\Account::class, "sso_account_id", "id");
    }
}
