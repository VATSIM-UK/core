<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Airport.
 *
 * @property int $id
 * @property string $icao
 * @property string $name
 * @property string $country
 * @property float $latitude
 * @property float $longitude
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport icao($icao)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Airport whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Airport extends Model
{
    protected $table = 'smartcars_airport';
    protected $fillable = [
        'icao',
        'name',
        'country',
        'latitude',
        'longitude',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function findByIcao($icao)
    {
        return self::icao($icao)->first();
    }

    public function scopeIcao($query, $icao)
    {
        return $query->where('icao', 'LIKE', $icao);
    }
}
