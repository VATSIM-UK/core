<?php

namespace App\Models\Messages;

use App\Models\Model;
use App\Models\Mship\Account;

/**
 * App\Models\Messages\Thread
 *
 * @property int $id
 * @property string $subject
 * @property int $read_only
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread\Post[] $posts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereReadOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Thread extends Model
{
    protected $table = 'messages_thread';
    protected $primaryKey = 'id';
    protected $fillable = ['subject', 'read_only'];
    public $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function participants()
    {
        return $this->belongsToMany(Account::class, 'messages_thread_participant')
                    ->withPivot(['display_as', 'read_at', 'status'])->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(\App\Models\Messages\Thread\Post::class, 'thread_id', 'id');
    }
}
