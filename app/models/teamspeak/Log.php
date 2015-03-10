<?php

namespace Models\Teamspeak;

class Log extends \Models\aModel {

    protected $table = 'teamspeak_log';
    protected $primaryKey = 'id';
    protected $fillable = ['registration_id', 'type'];
    protected $dates = ['created_at', 'updated_at'];

    public function registration() {
        return $this->belongsTo("\Models\Teamspeak\Registration", "registration_id", "id");
    }

    public function scopeIdleMessage($query) {
        return $query->where('type', '=', 'idle_message');
    }

    public function scopeIdlePoke($query) {
        return $query->where('type', '=', 'idle_poke');
    }

    public function scopeIdleKick($query) {
        return $query->where('type', '=', 'idle_kick');
    }

    public function scopeNickWarn($query) {
        return $query->where('type', '=', 'nick_warn');
    }

    public function scopeNickKick($query) {
        return $query->where('type', '=', 'nick_kick');
    }

}
