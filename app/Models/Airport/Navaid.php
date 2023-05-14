<?php

namespace App\Models\Airport;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Airport\Navaid.
 *
 * @property int $id
 * @property int $airport_id
 * @property int $type
 * @property string|null $name
 * @property string|null $heading
 * @property string $ident
 * @property float $frequency
 * @property int $frequency_band
 * @property string|null $remarks
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Airport $airport
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereAirportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereFrequencyBand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Navaid whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Navaid extends Model
{
    protected $table = 'airport_navaids';

    protected $fillable = [
        'type',
        'name',
        'heading',
        'ident',
        'frequency',
        'frequency_band',
        'remarks',
    ];

    const FREQUENCY_BAND_MHZ = 1;

    const FREQUENCY_BAND_KHZ = 2;

    const TYPE_NDB = 1;

    const TYPE_VOR = 2;

    const TYPE_VORDME = 3;

    const TYPE_DME = 4;

    const TYPE_ILS = 5;

    const TYPE_TACAN = 6;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }

    public function getTypeAttribute($type)
    {
        switch ($type) {
            case self::TYPE_NDB:
                return 'NDB';
            case self::TYPE_VOR:
                return 'VOR';
            case self::TYPE_VORDME:
                return 'VOR/DME';
            case self::TYPE_DME:
                return 'DME';
            case self::TYPE_ILS:
                return 'ILS';
            case self::TYPE_TACAN:
                return 'TACAN';
            default:
                return '';
        }
    }

    public function getFrequencyBandAttribute($band)
    {
        switch ($band) {
            case self::FREQUENCY_BAND_MHZ:
                return 'MHz';
            case self::FREQUENCY_BAND_KHZ:
                return 'KHz';
            default:
                return '';
        }
    }
}
