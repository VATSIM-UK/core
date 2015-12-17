<?php

namespace App\Models\Teamspeak;

class Alias extends \App\Models\aModel {

    protected $table = 'teamspeak_alias';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at'];

    public function account() {
        return $this->belongsTo("\App\Models\Teamspeak\Registration", "account_id", "account_id");
    }

}
