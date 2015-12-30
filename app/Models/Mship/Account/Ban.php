<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends \App\Models\aTimelineEntry
{

    use SoftDeletes;

    protected $table      = 'mship_account_ban';
    protected $primaryKey = "account_ban_id";
    protected $dates      = ['period_start', 'period_finish', 'created_at', 'updated_at', 'deleted_at'];
    protected $touches    = ['account'];

    const TYPE_LOCAL   = 80;
    const TYPE_NETWORK = 90;

    public static function scopeIsNetwork($query){
        return $query->where("type", "=", self::TYPE_NETWORK);
    }

    public static function scopeIsLocal($query){
        return $query->where("type", "=", self::TYPE_LOCAL);
    }

    public static function scopeIsActive($query){
        return $query->where("period_finish", ">=", \Carbon\Carbon::now());
    }

    public static function scopeIsHistoric($query){
        return $query->where("period_finish", "<", \Carbon\Carbon::now());
    }

    public function account()
    {
        return $this->belongsTo('\App\Models\Mship\Account', 'account_id', 'account_id');
    }

    public function banner()
    {
        return $this->belongsTo('\App\Models\Mship\Account', 'banned_by', 'account_id');
    }

    public function reason()
    {
        return $this->belongsTo('\App\Models\Mship\Ban\Reason', 'reason_id', 'ban_reason_id');
    }

    public function notes(){
        return $this->morphMany(\App\Models\Mship\Account\Note::class, "attachment");
    }

    public function setPeriodAmountFromTS(){
        $diff = $this->period_start->diff($this->period_finish);

        /**
         * IF:
         * - We have hours AND minutes; OR
         * - We have days AND (minutes OR hours).
         * THEN:
         * - Whole thing is minutes.
         */
        if(($diff->h > 0 AND $diff->i != 0) OR ($diff->d > 0 AND ($diff->i != 0 OR $diff->h != 0))){
            $this->period_amount = $this->period_start->diffInMinutes($this->period_finish);
            $this->period_unit = 'M';
        } elseif($diff->d > 0){
            $this->period_amount = $this->period_start->diffInDays($this->period_finish);
            $this->period_unit = 'D';
        } elseif($diff->h > 0) {
            $this->period_amount = $this->period_start->diffInHours($this->period_finish);
            $this->period_unit = 'H';
        } else {
            $this->period_amount = $this->period_start->diffInMinutes($this->period_finish);
            $this->period_unit = 'M';
        }
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