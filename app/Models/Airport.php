<?php

namespace App\Models;

use App\Models\Airport\Navaid;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use App\Models\NetworkData\Atc;
use App\Models\NetworkData\Pilot;

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
        'major',
        'latitude',
        'longitude',
        'elevation',
        'description',
        'departure_procedures',
        'arrival_procedures',
        'vfr_procedures',
        'other_information',
    ];

    protected $casts = [
        'major' => 'boolean',
    ];

    const FIR_TYPE_EGTT = 1;
    const FIR_TYPE_EGPX = 2;

    public function scopeUK($query)
    {
        return $query->whereNotNull('fir_type');
    }

    public function scopeICAO($query, $icao)
    {
        return $query->where('icao', $icao);
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

    public function getControllersAttribute()
    {
        if ($this->stations->count() > 0) {
            return Atc::withCallsignIn(['%'.$this->icao.'%', $this->stations->pluck('callsign')])->online()->with('account')->get();
        }

        return Atc::withCallsign('%'.$this->icao.'%')->online()->with('account')->get();
    }

    public function getPilotsAttribute()
    {
        return Pilot::withinAirport($this->icao)->online()->with('account')->get();
    }

    public function getFirTypeAttribute($fir)
    {
        switch ($fir) {
            case self::FIR_TYPE_EGTT:
                return 'EGTT';
            case self::FIR_TYPE_EGPX:
                return 'EGPX';
            default:
                return '';
        }
    }

    public function hasProcedures()
    {
        return $this->departure_procedures || $this->arrival_procedures || $this->vfr_procedures;
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
