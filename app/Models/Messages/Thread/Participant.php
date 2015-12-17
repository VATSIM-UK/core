<?php

namespace App\Models\Messages\Thread;

use Illuminate\Database\Eloquent\Model;

class Participant extends \App\Models\aModel
{

    protected $table      = 'messages_thread_participant';
    protected $primaryKey = "thread_participant_id";
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
        return $this->belongsTo(\App\Models\Messages\Thread::class, "thread_id", "thread_id");
    }

    public function account(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id", "account_id");
    }
}