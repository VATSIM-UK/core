<?php

namespace App\Modules\Ais\Models;

use App\Models\aModel;

class Aerodrome extends aModel {

    protected $table = "ais_aerodrome";
    protected $fillable = [
        "sector_id",
        "icao", "iata", "name",
        "latitude", "longitude",
        "display",
    ];

    public function sector(){
        return $this->belongsTo(App\Modules\Ais\Models\Fir\Sector::class);
    }

    public function facilities(){
        return $this->hasMany(App\Modules\Ais\Models\Facility::class);
    }

}