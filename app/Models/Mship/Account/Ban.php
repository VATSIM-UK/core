<?php

namespace App\Models\Mship\Account;

use App\Enums\BanTypeEnum;
use App\Events\Mship\Bans\BanUpdated;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Mship\Account\Ban.
 *
 * @property int $id
 * @property int $account_id
 * @property int|null $banned_by
 * @property BanTypeEnum $type
 * @property int|null $reason_id
 * @property string $reason_extra
 * @property \Carbon\Carbon|null $period_start
 * @property \Carbon\Carbon|null $period_finish
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $repealed_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account|null $banner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
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
 * @property-read \App\Models\Mship\Ban\Reason|null $reason
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isHistoric()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isLocal()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isNetwork()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isNotRepealed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban isRepealed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereBannedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban wherePeriodFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereReasonExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereReasonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereRepealedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Ban whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Ban extends Model
{
    use HasFactory;

    protected $table = 'mship_account_ban';

    protected $primaryKey = 'id';

    protected $casts = [
        'period_start' => 'datetime',
        'period_finish' => 'datetime',
        'created_at' => 'datetime',
        'repealed_at' => 'datetime',
        'updated_at' => 'datetime',
        'type' => BanTypeEnum::class,
    ];

    protected $touches = ['account'];

    protected $trackedEvents = ['created', 'updated', 'deleted'];

    public static function scopeIsNetwork($query)
    {
        return $query->where('type', '=', BanTypeEnum::Network);
    }

    public static function scopeIsLocal($query)
    {
        return $query->where('type', '=', BanTypeEnum::Local);
    }

    public static function scopeIsActive($query)
    {
        return $query->isNotRepealed()->where(fn ($query) => $query->where('period_finish', '>=', \Carbon\Carbon::now())->orWhereNull('period_finish'));
    }

    public static function scopeIsInActive($query)
    {
        return $query->isHistoric()->orWhere(fn ($query) => $query->isRepealed());
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
        event(new BanUpdated($this));
    }

    public function getIsLocalAttribute()
    {
        return $this->type == BanTypeEnum::Local;
    }

    public function getIsNetworkAttribute()
    {
        return $this->type == BanTypeEnum::Network;
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

        return ! $this->is_repealed && (! $period_finish || $now->between($period_start, $period_finish));
    }

    public function getIsExpiredAttribute()
    {
        return ! $this->is_active;
    }

    public function getTypeStringAttribute()
    {
        return match ($this->type) {
            BanTypeEnum::Local => trans('mship.ban.type.local'),
            BanTypeEnum::Network => trans('mship.ban.type.network'),
            default => trans('mship.ban.type.unknown'),
        };
    }

    public function getPeriodAmountStringAttribute()
    {
        if (! $this->period_finish) {
            return;
        }

        return human_diff_string($this->period_start, $this->period_finish);
    }

    public function getPeriodLeftAttribute()
    {
        if (! $this->period_finish) {
            return;
        }

        return Carbon::now()->diffInSeconds($this->period_finish, true);
    }

    public function getDisplayValueAttribute()
    {
        // TODO: Implement getDisplayValueAttribute() method.
    }
}
