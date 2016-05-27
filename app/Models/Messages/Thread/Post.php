<?php

namespace App\Models\Messages\Thread;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\Thread\Post
 *
 * @property integer $id
 * @property integer $thread_id
 * @property integer $account_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Messages\Thread $thread
 * @property-read \App\Models\Mship\Account $author
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereThreadId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread\Post whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Post extends \App\Models\aModel
{

    protected $table      = 'messages_thread_post';
    protected $primaryKey = "id";
    protected $fillable   = ["content"];
    public    $dates      = ['created_at', 'updated_at'];
    public    $timestamps = true;

    public function thread(){
        return $this->belongsTo(\App\Models\Messages\Thread::class, "thread_id", "id");
    }

    public function author(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id");
    }
}