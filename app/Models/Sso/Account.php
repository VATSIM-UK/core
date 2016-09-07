<?php

namespace App\Models\Sso;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sso\Account
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $api_key_public
 * @property string $api_key_private
 * @property string $salt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Token[] $tokens
 * @property-read mixed $display_value
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereApiKeyPublic($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereApiKeyPrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereSalt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Account extends \App\Models\Model
{

    use RecordsActivity;

    protected $table = "sso_account";
    protected $primaryKey = "id";
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['account_id'];

    public function tokens()
    {
        return $this->hasMany(\App\Models\Sso\Token::class, "sso_account_id", "id");
    }

    public function getDisplayValueAttribute()
    {
        return "NOT YET DEFINED IN __ACCOUNT__ MODELS";
    }
}
