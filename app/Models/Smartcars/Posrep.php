<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Posrep
 *
 * @property int $id
 * @property int $bid_id
 * @property int $aircraft_id
 * @property string $route
 * @property int $altitude
 * @property int $heading_mag
 * @property int $heading_true
 * @property float $latitude
 * @property float $longitude
 * @property int $groundspeed
 * @property int $distance_remaining
 * @property int $phase
 * @property string $time_departure
 * @property string $time_remaining
 * @property string $time_arrival
 * @property string $network
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Smartcars\Aircraft $aircraft
 * @property-read \App\Models\Smartcars\Flight $bid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereAircraftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereBidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereDistanceRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereHeadingMag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereHeadingTrue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereNetwork($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep wherePhase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereTimeArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereTimeDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereTimeRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Posrep whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Posrep extends Model
{
    protected $table = 'smartcars_posrep';
    protected $fillable = [
        'bid_id',
        'flight_id',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function bid()
    {
        return $this->hasOne(\App\Models\Smartcars\Flight::class, 'id', 'flight_id');
    }

    public function aircraft()
    {
        return $this->hasOne(\App\Models\Smartcars\Aircraft::class, 'id', 'aircraft_id');
    }
}
