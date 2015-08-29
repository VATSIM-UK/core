<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class Messages extends \App\Models\aModel
{

    protected $table      = 'messages_thread';
    protected $primaryKey = "thread_id";
    protected $fillable   = ["subject", "read_only"];
    public    $dates      = ['created_at', 'updated_at'];
    public    $timestamps = true;

    public function participants(){
        return $this->hasManyThrough(App\Models\Mship\Account::class, App\Models\Messages\Thread\Participant::class, "thread_id", "account_id");
    }

    public function posts(){
        return $this->hasMany(App\Models\Messages\Thread\Post::class, "thread_id", "thread_id");
    }

}