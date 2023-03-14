<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\FlightResource.
 *
 * @property int $id
 * @property int $flight_id
 * @property string $type
 * @property string $display_name
 * @property string $resource
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Smartcars\Flight $flight
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereFlightId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereResource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightResource whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FlightResource extends Model
{
    protected $table = 'smartcars_flight_resources';

    protected $fillable = ['display_name', 'resource', 'type'];

    public function flight()
    {
        return $this->belongsTo(\App\Models\Smartcars\Flight::class, 'flight_id', 'id');
    }

    public function setTypeAttribute($value)
    {
        if ($value !== 'uri' && $value !== 'file') {
            throw new \UnexpectedValueException("Invalid type: $value");
        }

        $this->attributes['type'] = $value;
    }

    public function asset()
    {
        if ($this->type === 'file') {
            return asset('storage/'.$this->resource);
        }

        return $this->resource;
    }
}
