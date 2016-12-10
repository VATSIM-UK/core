<?php

namespace App\Modules\NetworkData\Models;

use Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\NetworkData\Events\AtcSessionEnded;
use App\Modules\NetworkData\Events\AtcSessionDeleted;
use App\Modules\NetworkData\Events\AtcSessionStarted;

/**
 * App\Modules\NetworkData\Models\Atc.
 *
 * @property int $id
 * @property int $account_id
 * @property string $callsign
 * @property int $qualification_id
 * @property bool $facility_type
 * @property \Carbon\Carbon $connected_at
 * @property string $disconnected_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereCallsign($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereQualificationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereFacilityType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereConnectedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereDisconnectedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc forAccountId($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc forQualificationId($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc withCallsign($callsign)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc online()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\NetworkData\Models\Atc offline()
 * @mixin \Eloquent
 */
class Atc extends Model
{
    use SoftDeletes;

    protected $table      = 'statistic_atc';
    protected $primaryKey = 'id';
    protected $fillable   = ['account_id', 'qualification_id', 'facility_type', 'callsign', 'frequency', 'connected_at', 'disconnected_at'];
    public $dates         = ['connected_at', 'disocnnected_at', 'created_at', 'updated_at', 'deleted_at'];
    public $timestamps    = true;

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            event(new AtcSessionStarted($model));
        });

        static::deleted(function ($model) {
            event(new AtcSessionDeleted($model));
        });
    }

    public static function scopeForAccountId($query, $id)
    {
        return $query->where('account_id', '=', $id);
    }

    public static function scopeForQualificationId($query, $id)
    {
        return $query->where('qualification_id', '=', $id);
    }

    public static function scopeWithCallsign($query, $callsign)
    {
        return $query->where('callsign', 'LIKE', $callsign);
    }

    public static function scopeOnline($query)
    {
        return $query->where('disconnected_at', 'IS', null);
    }

    public static function scopeOffline($query)
    {
        return $query->where('disconnected_at', 'IS NOT', null);
    }

    public function setDisconnectedAtAttribute($timestamp)
    {
        $this->attributes['disconnected_at'] = $timestamp;
        event(new AtcSessionEnded($this));
    }
}
