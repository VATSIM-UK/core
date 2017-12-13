<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\FlightCriteria
 *
 * @property int $id
 * @property int $flight_id
 * @property int $order
 * @property float|null $min_latitude
 * @property float|null $max_latitude
 * @property float|null $min_longitude
 * @property float|null $max_longitude
 * @property int|null $min_altitude
 * @property int|null $max_altitude
 * @property int|null $min_groundspeed
 * @property int|null $max_groundspeed
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereFlightId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMaxAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMaxGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMaxLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMaxLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMinAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMinGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMinLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereMinLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriteria whereOrder($value)
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
