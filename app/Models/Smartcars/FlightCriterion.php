<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\FlightCriterion
 *
 * @property int $id
 * @property int $flight_id
 * @property int $order
 * @property float|null $min_latitude
 * @property float|null $max_latitude
 * @property float|null $min_longitude
 * @property float|null $max_longitude
 * @property int|null $min_altitude
 * @property int|null $max_altitude
 * @property int|null $min_groundspeed
 * @property int|null $max_groundspeed
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereFlightId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMaxAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMaxGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMaxLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMaxLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMinAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMinGroundspeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMinLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereMinLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\FlightCriterion whereOrder($value)
 * @mixin \Eloquent
 */
class FlightCriterion extends Model
{
    protected $table = 'smartcars_flight_criteria';

    protected $fillable = [
        'flight_id',
        'p1_latitude',
        'p1_longitude',
        'p2_latitude',
        'p2_longitude',
        'p3_latitude',
        'p3_longitude',
        'p4_latitude',
        'p4_longitude',
        'min_altitude',
        'max_altitude',
        'min_groundspeed',
        'max_groundspeed',
    ];

    public $timestamps = false;

    /**
     * Calculates whether the criteria contains the given point.
     *
     * Based on https://github.com/substack/point-in-polygon/blob/master/index.js
     *
     * @param float $latitude   The latitude of the point (vertical, y)
     * @param float $longitude  The longitude of the point (horizontal, x)
     * @return bool
     */
    public function hasPoint($latitude, $longitude)
    {
        $x = $longitude;
        $y = $latitude;
        $vs = [
            [$this->p1_longitude, $this->p1_latitude],
            [$this->p2_longitude, $this->p2_latitude],
            [$this->p3_longitude, $this->p3_latitude],
            [$this->p4_longitude, $this->p4_latitude],
        ];

        $inside = false;
        for ($i = 0, $j = sizeof($vs) - 1; $i < sizeof($vs); $j = $i++) {
            $xi = $vs[$i][0];
            $yi = $vs[$i][1];
            $xj = $vs[$j][0];
            $yj = $vs[$j][1];

            $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
