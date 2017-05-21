<?php

namespace App\Models\Messages\Thread;

/**
 * App\Models\Messages\Thread\Participant
 *
 * @property int $id
 * @property int $thread_id
 * @property int $account_id
 * @property string $display_as
 * @property int $status
 * @property \Carbon\Carbon $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Messages\Thread $thread
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isOwner()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isStatus($status)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isViewer()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereDisplayAs($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereReadAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereThreadId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Participant extends \App\Models\Model
{
    protected $table = 'messages_thread_participant';
    protected $primaryKey = 'id';
    protected $fillable = ['display_as'];
    public $dates = ['read_at', 'created_at', 'updated_at'];
    public $timestamps = true;

    const STATUS_OWNER = 90;
    const STATUS_VIEWER = 10;

    public static function scopeIsOwner($query)
    {
        return self::scopeIsStatus($query, self::STATUS_OWNER);
    }

    public static function scopeIsViewer($query)
    {
        return self::scopeIsStatus($query, self::STATUS_VIEWER);
    }

    public static function scopeIsStatus($query, $status)
    {
        return $query->where('status', '=', $status);
    }

    public function thread()
    {
        return $this->belongsTo(\App\Models\Messages\Thread::class, 'thread_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }
}
