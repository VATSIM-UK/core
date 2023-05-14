<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Session.
 *
 * @property int $id
 * @property string $session_id
 * @property int $account_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Mship\Account $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session accountId($accountId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session sessionId($sessionId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Session whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Session extends Model
{
    protected $table = 'smartcars_session';

    protected $fillable = [
        'session_id',
        'account_id',
    ];

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function findBySessionId($sessionID)
    {
        return self::sessionId($sessionID)->first();
    }

    public static function deleteOldSessions()
    {
        \DB::query('DELETE FROM smartcars_session WHERE updated_at < '.\Carbon\Carbon::now()->subHours(24));
    }

    public function scopeSessionId($query, $sessionId)
    {
        return $query->where('session_id', '=', $sessionId);
    }

    public function scopeAccountId($query, $accountId)
    {
        return $query->where('account_id', '=', $accountId);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id', 'id');
    }
}
