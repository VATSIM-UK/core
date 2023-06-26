<?php

namespace App\Models\NetworkData;

use App\Models\Airport;
use App\Models\Model;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\NetworkData\Pilot.
 *
 * @property int $id
 * @property int $account_id
 * @property string $callsign
 * @property string $flight_type
 * @property string $departure_airport
 * @property string $arrival_airport
 * @property string $alternative_airport
 * @property string $aircraft
 * @property string $cruise_altitude
 * @property string $cruise_tas
 * @property string $route
 * @property string $remarks
 * @property float|null $current_latitude
 * @property float|null $current_longitude
 * @property int|null $current_altitude
 * @property int|null $current_groundspeed
 * @property \Carbon\Carbon|null $departed_at
 * @property \Carbon\Carbon|null $arrived_at
 * @property \Carbon\Carbon|null $connected_at
 * @property \Carbon\Carbon|null $disconnected_at
 * @property int|null $minutes_online
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $human_duration
 * @property-read \App\Models\Mship\Qualification $qualification
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot offline()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot online()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereAircraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereAlternativeAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereArrivalAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereArrivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereConnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCruiseAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCruiseTas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCurrentAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCurrentGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCurrentLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereCurrentLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereDepartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereDepartureAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereDisconnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereFlightType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereMinutesOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Pilot withinDivision()
 *
 * @mixin \Eloquent
 */
class Pilot extends Model
{
    use Rememberable;

    protected $table = 'networkdata_pilots';

    protected $primaryKey = 'id';

    public $dates = ['departed_at', 'arrived_at', 'connected_at', 'disconnected_at', 'created_at', 'updated_at'];

    protected $fillable = [
        'account_id',
        'callsign',
        'flight_type',
        'departure_airport',
        'arrival_airport',
        'connected_at',
        'disconnected_at',
        'qualification_id',
        'alternative_airport',
        'aircraft',
        'cruise_altitude',
        'cruise_tas',
        'route',
        'remarks',
        'current_latitude',
        'current_longitude',
        'current_altitude',
        'current_groundspeed',
        'current_heading',
    ];

    public static function scopeOnline($query)
    {
        return $query->whereNull('disconnected_at');
    }

    public function getHumanDurationAttribute()
    {
        return \Carbon\Carbon::now()->subMinutes($this->minutes_online)->diffForHumans(null, true);
    }

    public static function scopeOffline($query)
    {
        return $query->whereNotNull('disconnected_at');
    }

    public static function scopeWithinDivision($query)
    {
        return $query->where(function ($subQuery) {
            return $subQuery->where('departure_airport', 'LIKE', 'EG%')
                ->orWhere('arrival_airport', 'LIKE', 'EG%');
        });
    }

    public static function scopeWithinAirport($query, $icao)
    {
        return $query->where(function ($subQuery) use ($icao) {
            return $subQuery->where('departure_airport', $icao)
                ->orWhere('arrival_airport', $icao);
        });
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id', 'id');
    }

    public function qualification()
    {
        return $this->belongsTo(\App\Models\Mship\Qualification::class);
    }

    public function isOnline()
    {
        return $this->attributes['disconnected_at'] === null;
    }

    public function isAtAirport(Airport $airport = null)
    {
        if (is_null($airport)) {
            return false;
        }

        $location = $airport->containsCoordinates($this->current_latitude, $this->current_longitude);
        $altitude = $this->current_altitude < $airport->elevation + 500;

        return $location && $altitude;
    }

    public function setDisconnectedAtAttribute($timestamp)
    {
        $this->attributes['disconnected_at'] = $timestamp;

        if (! is_null($timestamp)) {
            $this->current_altitude = null;
            $this->current_groundspeed = null;
            $this->current_latitude = null;
            $this->current_longitude = null;
        }

        $this->calculateTimeOnline();
    }

    /**
     * Calculate the total number of minutes the user spent online.
     * This will take into account whether the user has filed
     * multiple flight plans within the same session.
     */
    protected function calculateTimeOnline()
    {
        if (! is_null($this->disconnected_at)) {
            $firstFlightplan = self::where('account_id', $this->account_id)
                ->where('callsign', $this->callsign)
                ->where('connected_at', $this->connected_at)
                ->orderBy('created_at', 'ASC')
                ->first();

            // If this session was the first flight plan filed, the time online
            // is calculated from their connected_at time. If they changed
            // their flight plan, we'll use the time they changed it.
            if ($this->id === $firstFlightplan->id) {
                $this->minutes_online = $this->connected_at->diffInMinutes($this->disconnected_at);
            } else {
                $this->minutes_online = $this->created_at->diffInMinutes($this->disconnected_at);
            }
        }
    }
}
