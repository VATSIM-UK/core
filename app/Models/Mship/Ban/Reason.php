<?php

namespace App\Models\Mship\Ban;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Ban\Reason.
 *
 * @property int $id
 * @property string $name
 * @property string $reason_text
 * @property int $period_amount
 * @property string $period_unit
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $period_hours
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason wherePeriodAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason wherePeriodUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereReasonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Ban\Reason whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Reason extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'mship_ban_reason';

    public $timestamps = true;

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function bans()
    {
        return $this->hasMany(\App\Models\Mship\Account\Ban::class, 'reason_id', 'id');
    }

    public function getPeriodHoursAttribute()
    {
        if ($this->attributes['period_unit'] == 'H') {
            return $this->attributes['period_amount'];
        }

        if ($this->attributes['period_unit'] == 'D') {
            return $this->attributes['period_amount'] * 24;
        }

        if ($this->attributes['period_unit'] == 'M') {
            return $this->attributes['period_amount'] * 730.001;
        }
    }

    public function __toString()
    {
        return $this->name.' (Duration '.$this->period_amount.$this->period_unit.')';
    }
}
