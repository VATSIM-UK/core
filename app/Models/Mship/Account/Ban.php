<?php

namespace App\Models\Mship\Account;

use Carbon\Carbon;
use App\Traits\RecordsActivity;

/**
 * App\Models\Mship\Account\Ban
 *
 * @property int $id
 * @property int $account_id
 * @property int $banned_by
 * @property int $type
 * @property int $reason_id
 * @property string $reason_extra
 * @property \Carbon\Carbon $period_start
 * @property \Carbon\Carbon $period_finish
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $repealed_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account $banner
 * @property-read mixed $display_value
 * @property-read mixed $is_active
 * @property-read mixed $is_expired
 * @property-read mixed $is_local
 * @property-read mixed $is_network
 * @property-read mixed $is_repealed
 * @property-read mixed $period_amount_string
 * @property-read mixed $period_left
 * @property-read mixed $type_string
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read \App\Models\Mship\Ban\Reason $reason
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isActive()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isHistoric()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isLocal()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isNetwork()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isNotRepealed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban isRepealed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereBannedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban wherePeriodFinish($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban wherePeriodStart($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereReasonExtra($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereReasonId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereRepealedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Ban whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ban extends \App\Models\Model
{
    use RecordsActivity;

    protected $table = 'mship_account_ban';
    protected $primaryKey = 'id';
    protected $dates = ['period_start', 'period_finish', 'created_at', 'repealed_at', 'updated_at'];
    protected $touches = ['account'];

    const TYPE_LOCAL = 80;
    const TYPE_NETWORK = 90;

    public static function scopeIsNetwork($query)
    {
        return $query->where('type', '=', self::TYPE_NETWORK);
    }

    public static function scopeIsLocal($query)
    {
        return $query->where('type', '=', self::TYPE_LOCAL);
    }

    public static function scopeIsActive($query)
    {
        return $query->isNotRepealed()->where('period_finish', '>=', \Carbon\Carbon::now())->orWhereNull('period_finish');
    }

    public static function scopeIsHistoric($query)
    {
        return $query->isNotRepealed()->where('period_finish', '<', \Carbon\Carbon::now());
    }

    public static function scopeIsRepealed($query)
    {
        return $query->whereNotNull('repealed_at');
    }

    public static function scopeIsNotRepealed($query)
    {
        return $query->whereNull('repealed_at');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }

    public function banner()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'banned_by');
    }

    public function reason()
    {
        return $this->belongsTo(\App\Models\Mship\Ban\Reason::class, 'reason_id', 'id');
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, 'attachment');
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

        return !$period_finish || ($now->between($period_start, $period_finish) && !$this->is_repealed);
    }

    public function getIsExpiredAttribute()
    {
        return !$this->is_active;
    }

    public function getTypeStringAttribute()
    {
        switch ($this->attributes['type']) {
            case self::TYPE_LOCAL:
                return trans('mship.ban.type.local');
                break;
            case self::TYPE_NETWORK:
                return trans('mship.ban.type.network');
                break;
            default:
                return trans('mship.ban.type.unknown');
                break;
        }
    }

    public function getPeriodAmountStringAttribute()
    {
        if (!$this->period_finish) {
            return null;
        }

        return human_diff_string($this->period_start, $this->period_finish);
    }

    public function getPeriodLeftAttribute()
    {
        if (!$this->period_finish) {
            return null;
        }

        return Carbon::now()->diffInSeconds($this->period_finish, true);
    }

    public function getDisplayValueAttribute()
    {
        // TODO: Implement getDisplayValueAttribute() method.
    }
}
