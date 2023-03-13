<?php

namespace App\Models\Airport;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Airport\Runway.
 *
 * @property int $id
 * @property int $airport_id
 * @property string $ident
 * @property string $heading
 * @property int $width
 * @property int $length
 * @property int $surface_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Airport $airport
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Airport\Procedure[] $procedures
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereAirportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereSurfaceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Runway whereWidth($value)
 *
 * @mixin \Eloquent
 */
class Runway extends Model
{
    protected $table = 'airport_runways';
    protected $fillable = [
        'ident',
        'heading',
        'width',
        'length',
        'surface_type',
    ];

    const SURFACE_TYPE_ASPHALT = 1;
    const SURFACE_TYPE_GRASS = 2;
    const SURFACE_TYPE_CONCRETE = 3;
    const SURFACE_TYPE_SAND = 4;
    const SURFACE_TYPE_GRE = 5;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function getSurfaceTypeAttribute($type)
    {
        switch ($type) {
            case self::SURFACE_TYPE_ASPHALT:
                return 'Asphalt';
            case self::SURFACE_TYPE_GRASS:
                return 'Grass';
            case self::SURFACE_TYPE_CONCRETE:
                return 'Concrete';
            case self::SURFACE_TYPE_SAND:
                return 'Sand';
            case self::SURFACE_TYPE_GRE:
                return 'Graded/Rolled Earth';
            default:
                return 'Unknown';
        }
    }
}
