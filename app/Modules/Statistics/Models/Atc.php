<?php

namespace App\Modules\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Event;
use App\Modules\Statistics\Events\AtcSessionEnded;

class Atc extends Model
{
    use SoftDeletes;

    protected $table      = "statistics_atc";
    protected $primaryKey = "id";
    protected $fillable   = ["account_id", "qualification_id", "callsign", "connected_at", "disconnected_at"];
    public    $dates      = ["connected_at", "disocnnected_at", "created_at", "updated_at", "deleted_at"];
    public    $timestamps = true;

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

    public static function firstOrCreate($id, $attributes = [])
    {
        parent::firstOrCreate($id, $attributes);
    }

    public function setDisconnectedAtAttribute($timestamp)
    {
        $this->attributes['disconnected_at'] = $timestamp;
        Event::fire(new AtcSessionEnded($this));
    }
}