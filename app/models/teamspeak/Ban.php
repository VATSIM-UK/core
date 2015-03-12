<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Ban extends \Models\aModel {

    protected $table = 'teamspeak_ban';
    protected $primaryKey = 'id';

    public function account() {
        return $this->belongsTo("\Models\Teamspeak\Account", "account_id", "account_id");
    }

}
