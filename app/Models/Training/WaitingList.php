<?php

namespace App\Models\Training;

use App\Events\Training\WaitingListCreated;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingList extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            event(new WaitingListCreated($model));
        });
    }

    public $table = 'training_waiting_list';

    protected $dates = ['deleted_at'];

    const ATC_DEPARTMENT = 'atc';

    const PILOT_DEPARTMENT = 'pilot';

    const ALL_FLAGS = 'all';

    const ANY_FLAGS = 'any';

    protected $casts = [
        'feature_toggles' => 'array',
    ];

    /**
     * A Waiting List can be managed by many Staff Members (Accounts).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_staff',
            'list_id',
            'account_id'
        )->withTimestamps();
    }

    /**
     * Many WaitingLists can have many Accounts (pivot).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_account',
            'list_id'
        )->using(WaitingListAccount::class)
            ->withPivot([
                'id',
                'deleted_at',
                'notes',
                'created_at',
                'within_top_ten_notification_sent_at',
            ])->wherePivot('deleted_at', null);
    }

    public function accountsByEligibility($eligible = true)
    {
        return $this->accounts()
            ->orderByPivot('created_at')
            ->get()
            ->filter(function ($model) use ($eligible) {
                return $model->pivot->eligibility == $eligible;
            })->values();
    }

    /**
     * One WaitingList can have many flags associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flags()
    {
        return $this->hasMany(WaitingListFlag::class, 'list_id');
    }

    /**
     * Scope a query to only include ATC waiting lists.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtc($query)
    {
        return $query->where('department', '=', 'atc');
    }

    /**
     * Get the position of an account in the eligible waiting list.
     *
     * @return int|null
     */
    public function accountPosition(Account $account)
    {
        $key = $this->accountsByEligibility(true)->search(function ($accountItem) use ($account) {
            return $accountItem->id == $account->id;
        });

        return ($key !== false) ? $key + 1 : null;
    }

    /**
     * Associate a flag with a waiting list.
     *
     * @return mixed
     */
    public function addFlag(WaitingListFlag $flag)
    {
        $savedFlag = $this->flags()->save($flag);

        $this->accounts()->each(function ($account) use ($flag) {
            $account->pivot->flags()->attach($flag);
        });

        return $savedFlag;
    }

    /**
     * Remove a flag from a waiting list.
     *
     * @return mixed
     */
    public function removeFlag(WaitingListFlag $flag)
    {
        return $this->flags()->delete($flag);
    }

    /**
     * Add an Account to a waiting list.
     */
    public function addToWaitingList(Account $account, Account $staffAccount, Carbon $createdAt = null)
    {
        $timestamp = $createdAt != null ? $createdAt : Carbon::now();
        $this->accounts()->attach($account, ['added_by' => $staffAccount->id]);

        // the following code is required as the timestamp for created_at gets overridden during the creation
        // process, despite being disabled on the pivot!!
        $pivot = $this->accounts()->find($account->id)->pivot;
        $pivot->created_at = $timestamp;
        $pivot->save();
    }

    /**
     * Remove an Account from a waiting list.
     *
     * @return void
     */
    public function removeFromWaitingList(Account $account)
    {
        $waitingListAccount = $this->accounts()->where('account_id', $account->id)->first()->pivot;
        $waitingListAccount->delete();
    }

    public function isAtcList()
    {
        return $this->department == self::ATC_DEPARTMENT;
    }

    public function isPilotList()
    {
        return $this->department == self::PILOT_DEPARTMENT;
    }

    public function getFormattedDepartmentAttribute()
    {
        return match ($this->department) {
            self::ATC_DEPARTMENT => 'ATC Training',
            self::PILOT_DEPARTMENT => 'Pilot Training',
            default => ucfirst($this->department),
        };
    }

    public function getShouldCheckAtcHoursAttribute(): bool
    {
        return $this->feature_toggles['check_atc_hours'] ?? true;
    }

    public function getShouldCheckCtsTheoryExamAttribute(): bool
    {
        return $this->feature_toggles['check_cts_theory_exam'] ?? true;
    }

    public function getFeatureTogglesFormattedAttribute(): object
    {
        return (object) [
            'check_atc_hours' => $this->getShouldCheckAtcHoursAttribute(),
            'check_cts_theory_exam' => $this->getShouldCheckCtsTheoryExamAttribute(),
        ];
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
