<?php

namespace App\Modules\Ais\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $table      = 'ais_airport';
    protected $primaryKey = 'id';
    public    $timestamps = true;
    public    $dates      = ['created_at', 'updated_at', 'deleted_at'];
    public    $fillable   = [
        "sector_id",
        "icao",
        "iata",
        "name",
        "latitude",
        "longitude",
        "elevation",
        "continent",
        "country",
    ];
}
