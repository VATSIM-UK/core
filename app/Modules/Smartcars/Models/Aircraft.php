<?php

namespace App\Modules\Smartcars\Models;

use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    protected $table      = "smartcars_aircraft";
    protected $fillable   = [
        "icao",
        "name",
        "fullname",
        "registration",
        "range_nm",
        "weight_kg",
        "cruise_altitude",
        "max_passengers",
        "max_cargo_kg",
    ];
    public $timestamps = true;
    protected $dates      = [
        "created_at",
        "updated_at",
    ];

    public static function findByRegistration($reg){
        return Aircraft::registration($reg)->first();
    }

    public function scopeRegistration($query, $reg){
        return $query->where("registration", "LIKE", $reg);
    }
}
