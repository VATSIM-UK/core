<?php

namespace App\Models\Mship\Ban;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Ban\Reason
 *
 * @property int $id
 * @property string $name
 * @property string $reason_text
 * @property int $period_amount
 * @property string $period_unit
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bans
 * @property-read mixed $period_hours
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason wherePeriodAmount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason wherePeriodUnit($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereReasonText($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Ban\Reason whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Reason extends Model
{
    use SoftDeletes, RecordsActivity;
    protected $primaryKey = 'id';
    protected $table = 'mship_ban_reason';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

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
