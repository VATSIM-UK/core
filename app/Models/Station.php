<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Station.
 *
 * @property int $id
 * @property string $callsign
 * @property string $name
 * @property float $frequency
 * @property int $type
 * @property bool $sub_station
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Airport[] $airports
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereSubStation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Station whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Station extends Model
{
    protected $table = 'stations';
    protected $fillable = [
        'callsign',
        'name',
        'frequency',
        'type',
        'sub_station',
    ];
    protected $casts = [
        'sub_station' => 'boolean',
    ];

    const TYPE_ATIS = 1;
    const TYPE_DELIVERY = 2;
    const TYPE_GROUND = 3;
    const TYPE_TOWER = 4;
    const TYPE_APPROACH = 5;
    const TYPE_ENROUTE = 6;
    const TYPE_TERMINAL = 7;
    const TYPE_FSS = 8;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function airports()
    {
        return $this->belongsToMany(Airport::class, 'airport_stations');
    }

    public function getTypeAttribute($type)
    {
        switch ($type) {
            case self::TYPE_ATIS:
                return 'ATIS';
            case self::TYPE_DELIVERY:
                return 'Delivery';
            case self::TYPE_GROUND:
                return 'Ground';
            case self::TYPE_TOWER:
                return 'Tower';
            case self::TYPE_APPROACH:
                return 'Approach/Radar';
            case self::TYPE_ENROUTE:
                return 'Enroute';
            case self::TYPE_TERMINAL:
                return 'Terminal Control';
            case self::TYPE_FSS:
                return 'Flight Service Stations';
            default:
                return 'Unknown';
        }
    }
}
