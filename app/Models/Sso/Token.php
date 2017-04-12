<?php

namespace App\Models\Sso;

use App\Traits\RecordsActivity;

/**
 * App\Models\Sso\Token
 *
 * @property int $id
 * @property string $token
 * @property int $sso_account_id
 * @property string $return_url
 * @property int $account_id
 * @property string $request_ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $used_at
 * @property \Carbon\Carbon $updated_at
 * @property string $expires_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read mixed $display_value
 * @property-read mixed $is_expired
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token tokenValue($tokenValue)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token valid()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereRequestIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereReturnUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereSsoAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token whereUsedAt($value)
 * @mixin \Eloquent
 */
class Token extends \App\Models\Model
{
    use RecordsActivity;

    protected $table = 'sso_token';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'used_at'];
    protected $hidden = ['token_id'];

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }

    public function getIsExpiredAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->expires)->diffInSeconds() > 0;
    }

    public function scopeTokenValue($query, $tokenValue)
    {
        return $query->whereToken($tokenValue);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>=', \Carbon\Carbon::now()->toDateTimeString());
    }

    public function getDisplayValueAttribute()
    {
        return 'NOT YET DEFINED IN __TOKEN__ MODEL';
    }
}
