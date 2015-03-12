<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Confirmation extends \Models\aModel {

    protected $table = 'teamspeak_confirmation';
    protected $primaryKey = 'registration_id';

    public function registration() {
        return $this->belongsTo("\Models\Teamspeak\Registration", "registration_id", "id");
    }

}
