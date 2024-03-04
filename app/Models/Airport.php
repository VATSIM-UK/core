<?php

namespace App\Models;

use App\Models\Airport\Navaid;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use App\Models\Atc\Position;
use App\Models\NetworkData\Atc;
use App\Models\NetworkData\Pilot;

/**
 * App\Models\Airport.
 *
 * @property int $id
 * @property string|null $ident
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $elevation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereElevation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereLongitude($value)
 *
 * @mixin \Eloquent
 *
 * @property string|null $icao
 * @property string|null $iata
 * @property string|null $name
 * @property int|null $fir_type
 * @property bool $major
 * @property string|null $description
 * @property string|null $departure_procedures
 * @property string|null $arrival_procedures
 * @property string|null $vfr_procedures
 * @property string|null $other_information
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $controllers
 * @property-read mixed $pilots
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Airport\Navaid[] $navaids
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Airport\Procedure[] $procedures
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Airport\Runway[] $runways
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Atc\Position[] $positions
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport iCAO($icao)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport uK()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereArrivalProcedures($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereDepartureProcedures($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereFirType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereOtherInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereVfrProcedures($value)
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function navaids()
    {
        return $this->hasMany(Navaid::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runways()
    {
        return $this->hasMany(Runway::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'airport_positions');
    }

    public function getControllersAttribute()
    {
        if ($this->positions->count() > 0) {
            return Atc::withCallsignIn($this->positions->pluck('callsign')->push('%'.$this->icao.'%')->all())->online()->with('account')->get();
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

    /**
     * @return bool
     */
    public function hasProcedures()
    {
        return $this->departure_procedures || $this->arrival_procedures || $this->vfr_procedures;
    }

    /**
     * Determines whether a set of given decimal coordinates are close to the airport.
     *
     * @return bool
     */
    public function containsCoordinates($latitude, $longitude)
    {
        return $latitude < $this->latitude + 0.03 && $latitude > $this->latitude - 0.03
            && $longitude < $this->longitude + 0.05 && $longitude > $this->longitude - 0.05;
    }
}
