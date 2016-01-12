<?php

namespace App\Modules\Ais\Models\Fir;

use App\Models\aModel;
use App\Modules\Ais\Models\Fir;

class Sector extends aModel
{
    protected $table = "ais_fir_sector";
    protected $fillable = [
        "fir_id", "covered_by",
        "name", "callsign_default", "callsign_rule",
        "frequency",
    ];

    public function fir()
    {
        return $this->belongsTo(Fir::class);
    }

    public function coveredBy()
    {
        return $this->belongsTo(Sector::class);
    }
}