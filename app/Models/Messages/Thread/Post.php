<?php

namespace App\Models\Messages\Thread;

use Illuminate\Database\Eloquent\Model;

class Post extends \App\Models\aModel
{

    protected $table      = 'messages_thread_post';
    protected $primaryKey = "thread_post_id";
    protected $fillable   = ["content"];
    public    $dates      = ['created_at', 'updated_at'];
    public    $timestamps = true;

    public function thread(){
        return $this->belongsTo(\App\Models\Messages\Thread::class, "thread_id", "thread_id");
    }

    public function author(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id", "account_id");
    }
}