<?php

namespace App\Models\Sys;

/**
 * App\Models\Sys\Session
 *
 * @property string $id
 * @property string $payload
 * @property integer $last_activity
 * @property-read \App\Models\Mship\Account $account
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session wherePayload($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session whereLastActivity($value)
 * @mixin \Eloquent
 * @property integer $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session whereIpAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Session whereUserAgent($value)
 */
class Session extends \App\Models\Model
{
    protected $table = "sys_sessions";
    protected $primaryKey = "id";
    protected $hidden = ['session_id'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "session_id", "id");
    }
}
