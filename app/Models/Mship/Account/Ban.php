<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Account\Ban
 *
 * @property integer $account_ban_id
 * @property integer $account_id
 * @property integer $banned_by
 * @property integer $type
 * @property integer $reason_id
 * @property string $reason_extra
 * @property \Carbon\Carbon $period_start
 * @property \Carbon\Carbon $period_finish
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $repealed_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account $banner
 * @property-read \App\Models\Mship\Ban\Reason $reason
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read mixed $is_local
 * @property-read mixed $is_network
 * @property-read mixed $is_repealed
 * @property-read mixed $is_active
 * @property-read mixed $is_expired
 * @property-read mixed $type_string
 * @property-read mixed $period_amount_string
 * @property-read mixed $display_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesOwner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesExtra
 * @property-read mixed $timeline_entries_recent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isNetwork()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isLocal()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isActive()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isHistoric()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isRepealed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isNotRepealed()
 */
class Ban extends \App\Models\aModel
{

    use SoftDeletes;

    protected $table      = 'mship_account_ban';
    protected $primaryKey = "account_ban_id";
    protected $dates      = ['period_start', 'period_finish', 'created_at', 'repealed_at', 'updated_at', 'deleted_at'];
    protected $touches    = ['account'];

    const TYPE_LOCAL   = 80;
    const TYPE_NETWORK = 90;

    public static function scopeIsNetwork($query)
    {
        return $query->where("type", "=", self::TYPE_NETWORK);
    }

    public static function scopeIsLocal($query)
    {
        return $query->where("type", "=", self::TYPE_LOCAL);
    }

    public static function scopeIsActive($query)
    {
        return $query->isNotRepealed()->where("period_finish", ">=", \Carbon\Carbon::now());
    }

    public static function scopeIsHistoric($query)
    {
        return $query->isNotRepealed()->where("period_finish", "<", \Carbon\Carbon::now());
    }

    public static function scopeIsRepealed($query)
    {
        return $query->whereNotNull("repealed_at");
    }

    public static function scopeIsNotRepealed($query)
    {
        return $query->whereNull("repealed_at");
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

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, "attachment");
    }

    public function repeal()
    {
        $this->repealed_at = \Carbon\Carbon::now();
        $this->save();
    }

    public function getIsLocalAttribute()
    {
        return $this->type == self::TYPE_LOCAL;
    }

    public function getIsNetworkAttribute()
    {
        return $this->type == self::TYPE_NETWORK;
    }

    public function getIsRepealedAttribute()
    {
        return $this->repealed_at != null;
    }

    public function getIsActiveAttribute()
    {
        $period_start = $this->period_start;
        $period_finish = $this->period_finish;
        $now = \Carbon\Carbon::now();

        return ($now->between($period_start, $period_finish) && !$this->is_repealed);
    }

    public function getIsExpiredAttribute()
    {
        return !$this->is_active;
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

    public function getPeriodAmountStringAttribute()
    {
        return human_diff_string($this->period_start, $this->period_finish);
    }

    public function getDisplayValueAttribute()
    {
        // TODO: Implement getDisplayValueAttribute() method.
    }
}