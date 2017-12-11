<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\FlightCriteria
 *
 * @mixin \Eloquent
 */
class FlightCriteria extends Model
{
    protected $table = 'smartcars_flight_criteria';

    protected $fillable = [
        'flight_id',
        'min_latitude',
        'max_latitude',
        'min_longitude',
        'max_longitude',
        'min_altitude',
        'max_altitude',
        'min_groundspeed',
        'max_groundspeed',
    ];

    public $timestamps = false;
}
