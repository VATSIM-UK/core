<?php

namespace App\Modules\Ais\Models;

use App\Models\aModel;

class Facility extends aModel {

    protected $table = "ais_facility";
    protected $fillable = [
        "name"
    ];

    public function positions(){
        return $this->hasMany(Facility\Position::class);
    }
}