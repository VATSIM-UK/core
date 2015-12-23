<?php

namespace App\Modules\Statistics\Models;

use App\Modules\Statistics\Events\AtcSessionStarted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Event;
use App\Modules\Statistics\Events\AtcSessionEnded;
use App\Modules\Statistics\Events\AtcSessionDeleted;

class Atc extends Model
{
    use SoftDeletes;

    protected $table      = "statistic_atc";
    protected $primaryKey = "id";
    protected $fillable   = ["account_id", "qualification_id", "callsign", "connected_at", "disconnected_at"];
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
//
//    public static function firstOrCreate($attributes = [])
//    {
//        parent::firstOrCreate($attributes);
//    }

    public function setDisconnectedAtAttribute($timestamp)
    {
        $this->attributes['disconnected_at'] = $timestamp;
        event(new AtcSessionEnded($this));
    }
}