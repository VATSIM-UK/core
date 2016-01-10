<?php

namespace App\Modules\Ais\Models\Facility;

use App\Models\aModel;

class Position extends aModel {

    protected $table = "ais_facility_position";
    protected $fillable = [
        "facility_id",
        "callsign", "frequency",
        "logon_order",
    ];

    public function facility(){
        return $this->belongsTo(App\Modules\Ais\Models\Facility::class);
    }
}