<?php

namespace App\Modules\Statistics\Models;

use App\Modules\Statistics\Events\AtcSessionStarted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Event;
use App\Modules\Statistics\Events\AtcSessionEnded;
use App\Modules\Statistics\Events\AtcSessionDeleted;

/**
 * App\Modules\Statistics\Models\Atc
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $callsign
 * @property integer $qualification_id
 * @property boolean $facility_type
 * @property \Carbon\Carbon $connected_at
 * @property string $disconnected_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Statistics\Models\Atc forAccountId($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Statistics\Models\Atc forQualificationId($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Statistics\Models\Atc withCallsign($callsign)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Statistics\Models\Atc online()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Statistics\Models\Atc offline()
 */
class Atc extends Model
{
    use SoftDeletes;

    protected $table      = "statistic_atc";
    protected $primaryKey = "id";
    protected $fillable   = ["account_id", "qualification_id", "facility_type", "callsign", "connected_at", "disconnected_at"];
    public    $dates      = ["connected_at", "disocnnected_at", "created_at", "updated_at", "deleted_at"];
    public    $timestamps = true;

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            event(new AtcSessionStarted($model));
        });

        static::deleted(function($model){
            event(new AtcSessionDeleted($model));
        });

    }

    public static function scopeForAccountId($query, $id)
    {
        return $query->where("account_id", "=", $id);
    }

    public static function scopeForQualificationId($query, $id)
    {
        return $query->where("qualification_id", "=", $id);
    }

    public static function scopeWithCallsign($query, $callsign)
    {
        return $query->where("callsign", "LIKE", $callsign);
    }

    public static function scopeOnline($query)
    {
        return $query->where("disconnected_at", "IS", null);
    }

    public static function scopeOffline($query)
    {
        return $query->where("disconnected_at", "IS NOT", null);
    }

    public function setDisconnectedAtAttribute($timestamp)
    {
        $this->attributes['disconnected_at'] = $timestamp;
        event(new AtcSessionEnded($this));
    }
}