<?php

namespace App\Modules\Ais\Models\Fir;

use App\Models\aModel;

class Sector extends aModel {

    protected $table = "ais_fir_sector";
    protected $fillable = [
        "fir_id", "covered_by",
        "name", "callsign_default", "callsign_rule",
        "frequency",
    ];

    public function fir(){
        return $this->belongsTo(App\Modules\Ais\Models\Fir::class);
    }

    public function coveredBy(){
        return $this->belongsTo(App\Modules\Ais\Models\Fir\Sector::class);
    }
}