<?php

namespace App\Models\Smartcars;

use App\Libraries\Storage\FteStorageWrapper;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Flight.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property bool $featured
 * @property string $flightnum
 * @property int $departure_id
 * @property int $arrival_id
 * @property string $route
 * @property string $route_details
 * @property int $aircraft_id
 * @property int $cruise_altitude
 * @property float $distance
 * @property float $flight_time
 * @property string $notes
 * @property bool $enabled
 * @property mixed $image
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Smartcars\Aircraft $aircraft
 * @property-read \App\Models\Smartcars\Airport $arrival
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Smartcars\FlightCriterion[] $criteria
 * @property-read \App\Models\Smartcars\Airport $departure
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Smartcars\FlightResource[] $resources
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight enabled()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight featured()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight icao($icao)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereAircraftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereArrivalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereCruiseAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereDepartureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereFlightTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereFlightnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereRouteDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Flight whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Flight extends Model
{
    protected $table = 'smartcars_flight';

    protected $fillable = [
        'code',
        'name',
        'description',
        'featured',
        'flightnum',
        'departure_id',
        'arrival_id',
        'route',
        'route_details',
        'aircraft_id',
        'cruise_altitude',
        'distance',
        'flight_time',
        'notes',
        'enabled',
    ];

    protected $casts = [
        'featured' => 'bool',
        'enabled' => 'bool',
    ];

    public static function findByIcao($icao)
    {
        return Airport::icao($icao)->first();
    }

    public function scopeIcao($query, $icao)
    {
        return $query->where('icao', 'LIKE', $icao);
    }

    public function departure()
    {
        return $this->belongsTo(\App\Models\Smartcars\Airport::class, 'departure_id', 'id');
    }

    public function arrival()
    {
        return $this->belongsTo(\App\Models\Smartcars\Airport::class, 'arrival_id', 'id');
    }

    public function aircraft()
    {
        return $this->belongsTo(\App\Models\Smartcars\Aircraft::class, 'aircraft_id', 'id');
    }

    public function criteria()
    {
        return $this->hasMany(FlightCriterion::class, 'flight_id', 'id');
    }

    public function resources()
    {
        return $this->hasMany(FlightResource::class, 'flight_id', 'id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function image()
    {
        return new FteStorageWrapper;
    }

    public function getImageAttribute($value)
    {
        return $value ? $this->image()->retrieve($value) : null;
    }

    public function setImageAttribute($newValue)
    {
        if (isset($this->attributes['image']) && $this->attributes['image'] != $newValue) {
            // Deletes the old image if the file has changed
            $this->image()->delete($this->attributes['image']);
        }
        $this->attributes['image'] = $newValue;
    }
}
