<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class Thread extends \App\Models\aModel
{

    protected $table      = 'messages_thread';
    protected $primaryKey = "thread_id";
    protected $fillable   = ["subject", "read_only"];
    public    $dates      = ['created_at', 'updated_at'];
    public    $timestamps = true;

    public function participants(){
        return $this->belongsToMany(\App\Models\Mship\Account::class, "messages_thread_participant", "thread_id", "account_id")->withPivot("display_as", "read_at", "status")->withTimestamps();
    }

    public function posts(){
        return $this->hasMany(\App\Models\Messages\Thread\Post::class, "thread_id", "thread_id");
    }

}