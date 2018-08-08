<?php

namespace App\Models;

use App\Models\Airport\Navaid;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;

/**
 * App\Models\Airport
 *
 * @property int $id
 * @property string|null $ident
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $elevation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereElevation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereLongitude($value)
 * @mixin \Eloquent
 */
class Airport extends Model
{
    public $table = 'airports';
    protected $fillable = [
        'icao',
        'iata',
        'name',
        'fir_type',
        'latitude',
        'longitude',
        'elevation',
        'description',
        'departure_procedures',
        'arrival_procedures',
        'vfr_procedures',
        'other_information',
    ];

    const FIR_TYPE_EGTT = 1;
    const FIR_TYPE_EGPX = 2;

    public function scopeUK($query)
    {
        return $query->whereNotNull('fir_type');
    }

    public function navaids()
    {
        return $this->hasMany(Navaid::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function runways()
    {
        return $this->hasMany(Runway::class);
    }

    public function stations()
    {
        return $this->belongsToMany(Station::class, 'airport_stations');
    }

    public function getFirTypeAttribute($fir)
    {
        switch ($fir) {
            case self::FIR_TYPE_EGTT:
                return "EGTT";
            case self::FIR_TYPE_EGPX:
                return "EGPX";
            default:
                return "";
        }
    }

    /**
     * Determines whether a set of given decimal coordinates are close to the airport.
     *
     * @param $latitude
     * @param $longitude
     * @return bool
     */
    public function containsCoordinates($latitude, $longitude)
    {
        return $latitude < $this->latitude + 0.03 && $latitude > $this->latitude - 0.03
            && $longitude < $this->longitude + 0.05 && $longitude > $this->longitude - 0.05;
    }
}
