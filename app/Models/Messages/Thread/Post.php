<?php

namespace App\Models\Messages\Thread;

/**
 * App\Models\Messages\Thread\Post
 *
 * @property int $id
 * @property int $thread_id
 * @property int $account_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $author
 * @property-read \App\Models\Messages\Thread $thread
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereThreadId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Post extends \App\Models\Model
{
    protected $table = 'messages_thread_post';
    protected $primaryKey = 'id';
    protected $fillable = ['content'];
    public $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function thread()
    {
        return $this->belongsTo(\App\Models\Messages\Thread::class, 'thread_id', 'id');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }
}
