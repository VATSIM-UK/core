<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $table      = 'smartcars_airport';
    protected $fillable   = [
        'icao',
        'name',
        'country',
        'latitude',
        'longitude',
    ];
    public $timestamps    = true;
    protected $dates      = [
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
