<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Airport
 *
 * @property int $id
 * @property string|null $ident
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $elevation
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
    public $timestamps = false;

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
