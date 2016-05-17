<?php

namespace App\Models\Messages\Thread;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\Thread\Participant
 *
 * @property integer $thread_participant_id
 * @property integer $thread_id
 * @property integer $account_id
 * @property string $display_as
 * @property integer $status
 * @property \Carbon\Carbon $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Messages\Thread $thread
 * @property-read \App\Models\Mship\Account $account
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isOwner()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isViewer()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Participant isStatus($status)
 */
class Participant extends \App\Models\aModel
{

    protected $table      = 'messages_thread_participant';
    protected $primaryKey = "id";
    protected $fillable   = ["display_as"];
    public    $dates      = ["read_at", 'created_at', 'updated_at'];
    public    $timestamps = true;

    const STATUS_OWNER = 90;
    const STATUS_VIEWER = 10;

    public static function scopeIsOwner($query){
        return self::scopeIsStatus($query, self::STATUS_OWNER);
    }

    public static function scopeIsViewer($query){
        return self::scopeIsStatus($query, self::STATUS_VIEWER);
    }

    public static function scopeIsStatus($query, $status){
        return $query->where("status", "=", $status);
    }

    public function thread(){
        return $this->belongsTo(\App\Models\Messages\Thread::class, "thread_id", "id");
    }

    public function account(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id");
    }
}