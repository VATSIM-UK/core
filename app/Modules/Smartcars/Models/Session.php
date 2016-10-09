<?php

namespace App\Modules\Smartcars\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table      = "smartcars_session";
    protected $fillable   = [
        "session_id",
        "account_id",
    ];
    public $timestamps = true;
    protected $dates      = [
        "created_at",
        "updated_at",
    ];

    public static function findBySessionId($sessionID){
        return Session::sessionId($sessionID)->first();
    }

    public static function deleteOldSessions(){
        \DB::query("DELETE FROM smartcars_session WHERE updated_at < ".\Carbon\Carbon::now()->subHours(24));
    }

    public function scopeSessionId($query, $sessionId){
        return $query->where("session_id", "=", $sessionId);
    }

    public function scopeAccountId($query, $accountId){
        return $query->where("account_id", "=", $accountId);
    }

    public function account(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id", "id");
    }
}
