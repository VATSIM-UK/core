<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Aircraft.
 *
 * @property int $id
 * @property string $icao
 * @property string $name
 * @property string $fullname
 * @property string $registration
 * @property int $range_nm
 * @property int $weight_kg
 * @property int $cruise_altitude
 * @property int $max_passengers
 * @property int $max_cargo_kg
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft registration($reg)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereCruiseAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereMaxCargoKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereMaxPassengers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereRangeNm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Aircraft whereWeightKg($value)
 *
 * @mixin \Eloquent
 */
class Aircraft extends Model
{
    protected $table = 'smartcars_aircraft';
    protected $fillable = [
        'icao',
        'name',
        'fullname',
        'registration',
        'range_nm',
        'weight_kg',
        'cruise_altitude',
        'max_passengers',
        'max_cargo_kg',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function findByRegistration($reg)
    {
        return self::registration($reg)->first();
    }

    public function scopeRegistration($query, $reg)
    {
        return $query->where('registration', 'LIKE', $reg);
    }
}
