<?php

namespace App\Modules\Smartcars\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $table      = "smartcars_flight";
    protected $fillable   = [
        "code",
        "flightnum",
        "departure_id",
        "arrival_id",
        "route",
        "route_details",
        "aircraft_id",
        "cruise_altitude",
        "distance",
        "flight_time",
        "notes",
        "enabled",
    ];
    public $timestamps = true;
    protected $dates      = [
        "created_at",
        "updated_at",
    ];

    public static function findByIcao($icao){
        return Airport::icao($icao)->first();
    }

    public function scopeIcao($query, $icao){
        return $query->where("icao", "LIKE", $icao);
    }

    public function departure(){
        return $this->belongsTo(\App\Modules\Smartcars\Models\Airport::class, "departure_id", "id");
    }

    public function arrival(){
        return $this->belongsTo(\App\Modules\Smartcars\Models\Airport::class, "arrival_id", "id");
    }

    public function aircraft(){
        return $this->belongsTo(\App\Modules\Smartcars\Models\Aircraft::class, "aircraft_id", "id");
    }
}
