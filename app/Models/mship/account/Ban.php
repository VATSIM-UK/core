<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends \Models\aTimelineEntry {

    protected $table = 'mship_account_ban';
    protected $primaryKey = "account_ban_id";
    public $timestamps = true;

    use SoftDeletes;

    protected $dates = ['period_start', 'period_finish', 'created_at', 'updated_at', 'deleted_at'];

    const TYPE_LOCAL = 80;
    const TYPE_NETWORK = 90;

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

    public function getTypeStringAttribute(){
        switch($this->attributes['type']){
            case self::TYPE_LOCAL:
                return "Local Ban";
                break;
            case self::TYPE_NETWORK:
                return "Network Ban";
                break;
            default:
                return "Unknown Ban";
                break;
        }
    }

    public function getPeriodUnitStringAttribute(){
        switch($this->attributes['period_unit']){
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