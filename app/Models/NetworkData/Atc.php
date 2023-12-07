<?php

namespace App\Models\NetworkData;

use App\Events\NetworkData\AtcSessionDeleted;
use App\Events\NetworkData\AtcSessionEnded;
use App\Events\NetworkData\AtcSessionStarted;
use App\Events\NetworkData\AtcSessionUpdated;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Malahierba\PublicId\PublicId;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\NetworkData\Atc.
 *
 * @property int $id
 * @property int $account_id
 * @property string $callsign
 * @property float|null $frequency
 * @property int $qualification_id
 * @property int|null $facility_type
 * @property \Carbon\Carbon|null $connected_at
 * @property \Carbon\Carbon|null $disconnected_at
 * @property int|null $minutes_online
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $account_name
 * @property-read mixed $human_duration
 * @property-read mixed $is_online
 * @property-read string $public_id
 * @property-read mixed $type
 * @property-read \App\Models\Mship\Qualification $qualification
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc forAccountId($id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc forQualificationId($id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc isUK()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc offline()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc onFrequency()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc online()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\NetworkData\Atc onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc thisYear()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereConnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereDisconnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereFacilityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereMinutesOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereQualificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc withCallsign($callsign)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\NetworkData\Atc withCallsignIn($callsigns)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\NetworkData\Atc withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\NetworkData\Atc withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Atc extends Model
{
    use PublicId, Rememberable, SoftDeletes;

    protected static $public_id_salt = 'vatsim-uk-network-data-atc-sessions';

    protected static $public_id_min_length = 10;

    protected static $public_id_alphabet = 'upper_alphanumeric';

    protected $table = 'networkdata_atc';

    protected $primaryKey = 'id';

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public $timestamps = true;

    protected $fillable = [
        'account_id',
        'qualification_id',
        'facility_type',
        'callsign',
        'frequency',
        'connected_at',
        'disconnected_at',
        'updated_at',
    ];

    protected $visible = [
        'public_id',
        'account_id',
        'account_name',
        'callsign',
        'frequency',
        'facility_type',
        'connected_at',
        'updated_at',
    ];

    protected $appends = [
        'publicId' => 'public_id',
        'accountName' => 'account_name',
        'ukSession' => 'uk_session',
    ];

    const TYPE_OBS = 1;

    const TYPE_DEL = 2;

    const TYPE_GND = 3;

    const TYPE_TWR = 4;

    const TYPE_APP = 5;

    const TYPE_DEP = 5;

    const TYPE_CTR = 6;

    const TYPE_FSS = 7;

    protected static function boot()
    {
        parent::boot();

        self::created(function ($atcSession) {
            event(new AtcSessionStarted($atcSession));
        });

        self::updated(function ($atcSession) {
            event(new AtcSessionUpdated($atcSession));

            if (! $atcSession->disconnected_at) {
                return;
            }
        });

        self::deleted(function ($atcSession) {
            event(new AtcSessionDeleted($atcSession));
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

    public static function scopeWithCallsignIn($query, array $callsigns)
    {
        return $query->where(function ($query) use ($callsigns) {
            foreach ($callsigns as $callsign) {
                $query->orWhere('callsign', 'LIKE', $callsign);
            }
        });
    }

    public static function scopeOnline($query)
    {
        return $query->whereNull('disconnected_at');
    }

    public static function scopeOffline($query)
    {
        return $query->whereNotNull('disconnected_at');
    }

    public static function scopeOnFrequency($query)
    {
        return $query->whereNotNull('frequency');
    }

    public static function scopeThisYear($query)
    {
        $startOfYear = \Carbon\Carbon::parse('first day of year');

        return $query->where('connected_at', '>=', $startOfYear);
    }

    public function scopeAccountIsPartOfUk($query)
    {
        return $query->join('mship_account_state', function ($join) {
                $join->on('mship_account_state.account_id', '=', 'networkdata_atc.account_id')
                    ->whereNull('mship_account_state.end_at');
            })
            ->join('mship_state', function ($join) {
                $join->on('mship_state.id', '=', 'mship_account_state.state_id')
                    ->whereIn('mship_state.code', ['DIVISION', 'VISITING', 'TRANSFERRING']);
            });
    }

    public static function scopeIsUK($query)
    {
        return $query->where(function ($subQuery) {
            return $subQuery->where('callsign', 'LIKE', 'EG%')
                ->orWhere('callsign', 'LIKE', "SCO\_%")
                ->orWhere('callsign', 'LIKE', "STC\_%")
                ->orWhere('callsign', 'LIKE', "LON\_%")
                ->orWhere('callsign', 'LIKE', "LTC\_%")
                ->orWhere('callsign', 'LIKE', 'MAN\_%')
                ->orWhere('callsign', 'LIKE', 'LXGB_%')
                ->orWhere('callsign', 'LIKE', 'LCRA_%')
                ->orWhere('callsign', 'LIKE', 'FHAW_%')
                ->orWhere('callsign', 'LIKE', 'THAMES\_%')
                ->orWhere('callsign', 'LIKE', 'ESSEX\_%')
                ->orWhere('callsign', 'LIKE', 'SOLENT\_%');
        });
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id', 'id');
    }

    public function qualification()
    {
        return $this->belongsTo(\App\Models\Mship\Qualification::class);
    }

    public function getAccountNameAttribute()
    {
        return $this->account->name;
    }

    public function getIsOnlineAttribute()
    {
        return $this->attributes['disconnected_at'] === null;
    }

    public function getTypeAttribute()
    {
        switch ($this->attributes['facility_type']) {
            case self::TYPE_OBS:
                return trans('atc.type.obs');
            case self::TYPE_DEL:
                return trans('atc.type.del');
            case self::TYPE_GND:
                return trans('atc.type.gnd');
            case self::TYPE_TWR:
                return trans('atc.type.twr');
            case self::TYPE_APP:
                return trans('atc.type.app');
            case self::TYPE_CTR:
                return trans('atc.type.ctr');
            case self::TYPE_FSS:
                return trans('atc.type.fss');
            default:
                return 'Unknown';
        }
    }

    public function getFrequencyAttribute()
    {
        return number_format($this->attributes['frequency'], 3);
    }

    public function disconnectAt($timestamp)
    {
        $this->disconnected_at = $timestamp;
        $this->save();

        $this->calculateTimeOnline();

        event(new AtcSessionEnded($this));
    }

    public function getHumanDurationAttribute()
    {
        return \Carbon\Carbon::now()->subMinutes($this->minutes_online)->diffForHumans(null, true);
    }

    public function getUkSessionAttribute()
    {
        return $this::isUK()->get()->contains($this);
    }

    /**
     * Calculate the total number of minutes the user spent online
     * When called this will calculate the total difference in
     * minutes and persist/save the value to the database.
     */
    public function calculateTimeOnline()
    {
        if (! $this->disconnected_at) {
            return;
        }

        $this->minutes_online = $this->connected_at->diffInMinutes($this->disconnected_at);

        return $this->save();
    }
}
