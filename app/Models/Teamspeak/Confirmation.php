<?php

namespace App\Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Confirmation extends \App\Models\aModel {

    protected $table = 'teamspeak_confirmation';
    protected $primaryKey = 'registration_id';

    public function registration() {
        return $this->belongsTo("\App\Models\Teamspeak\Registration", "registration_id", "id");
    }

}
