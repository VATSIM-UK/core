<?php

namespace App\Models\Messages;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\Thread
 *
 * @property integer                                                                          $thread_id
 * @property string                                                                           $subject
 * @property boolean                                                                          $read_only
 * @property \Carbon\Carbon                                                                   $created_at
 * @property \Carbon\Carbon                                                                   $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Account[]                          $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread\Post[] $posts
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread whereThreadId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread whereReadOnly($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Thread extends \App\Models\aModel
{

    protected $table = 'messages_thread';
    protected $primaryKey = "thread_id";
    protected $fillable = ["subject", "read_only"];
    public $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function participants()
    {
        return $this->belongsToMany(Account::class, "messages_thread_participant", "thread_id")
                    ->withPivot("display_as", "read_at", "status")->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(\App\Models\Messages\Thread\Post::class, "thread_id", "thread_id");
    }

}