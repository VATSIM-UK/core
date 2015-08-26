<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends \Models\aTimelineEntry
{

    use SoftDeletes;

    protected $table      = 'mship_account_ban';
    protected $primaryKey = "account_ban_id";
    protected $dates      = ['period_start', 'period_finish', 'created_at', 'updated_at', 'deleted_at'];
    protected $touches    = ['account'];

    const TYPE_LOCAL   = 80;
    const TYPE_NETWORK = 90;

    public static function scopeIsActive($query){
        return $query->where("period_finish", ">=", \Carbon\Carbon::now());
    }

    public static function scopeIsHistoric($query){
        return $query->where("period_finish", "<", \Carbon\Carbon::now());
    }

    public function account()
    {
        return $this->belongsTo('Models\Mship\Account', 'account_id', 'account_id');
    }

    public function banner()
    {
        return $this->belongsTo('Models\Mship\Account', 'banned_by', 'account_id');
    }

    public function reason()
    {
        return $this->belongsTo('\Models\Mship\Ban\Reason', 'reason_id', 'ban_reason_id');
    }

    public function getTypeStringAttribute()
    {
        switch ($this->attributes['type']) {
            case self::TYPE_LOCAL:
                return trans("mship.ban.type.local");
                break;
            case self::TYPE_NETWORK:
                return trans("mship.ban.type.network");
                break;
            default:
                return trans("mship.ban.type.unknown");
                break;
        }
    }

    public function getPeriodUnitStringAttribute()
    {
        switch ($this->attributes['period_unit']) {
            case "M":
                return "Minutes";
                break;
            case "H":
                return "Hours";
                break;
            case "D":
                return "Days";
                break;
            default:
                return "Unknown length";
                break;
        }
    }

    public function getDisplayValueAttribute()
    {
        // TODO: Implement getDisplayValueAttribute() method.
    }
}