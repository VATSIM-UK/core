<?php

namespace App\Modules\Ais\Models;

use App\Models\aModel;

/**
 * App\Modules\Ais\Models\Aerodrome
 *
 * @property-read \App\Modules\Ais\Models\Fir\Sector $sector
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Facility[] $facilities
 */
class Aerodrome extends aModel {

    protected $table = "ais_aerodrome";
    protected $fillable = [
        "sector_id",
        "icao", "iata", "name",
        "latitude", "longitude",
        "display",
    ];

    public function sector(){
        return $this->belongsTo(Fir\Sector::class);
    }

    public function facilities(){
        return $this->hasMany(Facility::class);
    }

}