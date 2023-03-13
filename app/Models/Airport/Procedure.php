<?php

namespace App\Models\Airport;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Airport\Procedure.
 *
 * @property int $id
 * @property int $airport_id
 * @property int|null $runway_id
 * @property int $type
 * @property string $ident
 * @property string|null $initial_fix
 * @property int|null $initial_altitude
 * @property int|null $final_altitude
 * @property string|null $remarks
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Airport $airport
 * @property-read mixed $procedure_type
 * @property-read \App\Models\Airport\Runway|null $runway
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereAirportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereFinalAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereInitialAltitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereInitialFix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereRunwayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereSID()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereSTAR()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport\Procedure whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Procedure extends Model
{
    protected $table = 'airport_procedures';
    protected $fillable = [
        'type',
        'ident',
        'initial_fix',
        'initial_altitude',
        'final_altitude',
        'remarks',
    ];

    const TYPE_SID = 1;
    const TYPE_STAR = 2;

    public function scopeWhereSID($query)
    {
        return $query->where('type', self::TYPE_SID);
    }

    public function scopeWhereSTAR($query)
    {
        return $query->where('type', self::TYPE_STAR);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function runway()
    {
        return $this->belongsTo(Runway::class);
    }

    public function getProcedureTypeAttribute()
    {
        switch ($this->type) {
            case self::TYPE_SID:
                return 'SID';
            case self::TYPE_STAR:
                return 'STAR';
            default:
                return '';
        }
    }
}
