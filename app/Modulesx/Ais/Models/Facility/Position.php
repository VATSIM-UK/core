<?php

namespace App\Modules\Ais\Models\Facility;

use App\Models\aModel;
use App\Modules\Ais\Models\Facility;

/**
 * App\Modules\Ais\Models\Facility\Position
 *
 * @property-read \App\Modules\Ais\Models\Facility $facility
 */
class Position extends aModel {

    protected $table = "ais_facility_position";
    protected $fillable = [
        "facility_id",
        "callsign", "frequency",
        "logon_order",
    ];

    public function facility(){
        return $this->belongsTo(Facility::class);
    }
}