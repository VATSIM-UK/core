<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Ban extends \Models\aModel {

    protected $table = 'teamspeak_ban';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'expires_at'];


    public function account() {
        return $this->belongsTo("\Models\Teamspeak\Account", "account_id", "account_id");
    }

}
