<?php

namespace App\Modules\Ais\Models\Fir;

use App\Models\aModel;
use App\Modules\Ais\Models\Fir;

/**
 * App\Modules\Ais\Models\Fir\Sector
 *
 * @property-read \App\Modules\Ais\Models\Fir $fir
 * @property-read \App\Modules\Ais\Models\Fir\Sector $coveredBy
 * @mixin \Eloquent
 */
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