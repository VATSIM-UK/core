<?php

namespace App\Models\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class WaitingListAccount extends Pivot
{
    use SoftDeletes;

    public $table = 'training_waiting_list_account';

    public $fillable = ['added_by', 'deleted_at', 'notes'];

    protected $appends = ['atcHourCheck'];

    // 24 hours
    protected $cacheTtl = 86400;

    public function status()
    {
        return $this->belongsToMany(
            WaitingListStatus::class,
            'training_waiting_list_account_status',
            'waiting_list_account_id',
            'status_id'
        )
            ->withPivot(['start_at', 'end_at'])->using(WaitingListAccountStatus::class)
            ->wherePivot('end_at', null);
    }

    public function flags()
    {
        return $this->belongsToMany(
            WaitingListFlag::class,
            'training_waiting_list_account_flag',
            'waiting_list_account_id',
            'flag_id'
        )->withPivot(['marked_at', 'id'])->using(WaitingListAccountFlag::class);
    }

    public function waitingList()
    {
        return $this->belongsTo(WaitingList::class, 'list_id');
    }

    public function pendingRemoval()
    {
        return $this->hasMany(WaitingListAccountPendingRemoval::class, 'waiting_list_account_id')->orderBy('created_at', 'desc');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * @param  \App\Models\Training\WaitingList\WaitingListStatus  $listStatus
     */
    public function addStatus(WaitingListStatus $listStatus)
    {
        $nonEnded = $this->status->reject(function ($value, $key) {
            return ! is_null($value->pivot->end_at);
        });

        $nonEnded->each(function ($item, $key) {
            $item->pivot->endStatus();
        });

        return $this->status()->attach($listStatus, ['start_at' => now()]);
    }

    /**
     * @param  \App\Models\Training\WaitingList\WaitingListStatus  $listStatus
     * @return int
     */
    public function removeStatus(WaitingListStatus $listStatus)
    {
        return $this->status()->detach($listStatus);
    }

    /**
     * @param  \App\Models\Training\WaitingList\WaitingListAccountPendingRemoval  $pendingRemoval
     */
    public function addPendingRemoval(Carbon $removalDate)
    {
        return $this->pendingRemoval()->create(['removal_date' => $removalDate]);
    }

    public function addFlag(WaitingListFlag $listFlag, $value = null)
    {
        return $this->flags()->attach($listFlag, ['marked_at' => $value]);
    }

    /**
     * Mark a Flag as true.
     */
    public function markFlag(WaitingListFlag $listFlag)
    {
        // retrieve the pivot model of WaitingListAccountFlag
        $flag = $this->flags()->get()->find($listFlag)->pivot;

        $flag->mark();
    }

    public function unMarkFlag(WaitingListFlag $listFlag)
    {
        $flag = $this->flags()->get()->find($listFlag)->pivot;

        if (! $flag->value) {
            return;
        }

        $flag->unMark();
    }

    public function getCurrentStatusAttribute()
    {
        return $this->status()->first();
    }

    public function getPendingRemovalAttribute()
    {
        return $this->pendingRemoval()->first();
    }

    public function getPositionAttribute()
    {
        return $this->waitingList->accountPosition($this->account);
    }

    public function recentATCMinutes()
    {
        $hourCheckKey = "{$this->cacheKey()}:recentAtcMins";

        if (Cache::has($hourCheckKey)) {
            return Cache::get($hourCheckKey);
        }

        // gather the sessions from the last 3 months in the UK (isUK scope)
        $hours = Atc::where('account_id', $this->account_id)
            ->whereDate('disconnected_at', '>=', Carbon::parse('3 months ago'))->isUk()->sum('minutes_online');
        Cache::put($hourCheckKey, $hours, $this->cacheTtl);

        return $hours;
    }

    public function atcHourCheck()
    {
        if ($this->waitingList->department === WaitingList::PILOT_DEPARTMENT) {
            return true;
        }

        if (! $this->waitingList->should_check_atc_hours) {
            return true;
        }

        // 12 hours is represented as 720 minutes
        $minutesRequired = 720;

        return $this->recentATCMinutes() >= $minutesRequired;
    }

    public function getAtcHourCheckAttribute()
    {
        return $this->atcHourCheck();
    }

    public function allFlagsChecker()
    {
        if ($this->waitingList->flags_check == WaitingList::ALL_FLAGS) {
            // iterate through each of the flags to see if they are true. If a false flag is detected, stop iterating.
            return $this->flags->every(function ($model) {
                return $model->pivot->value;
            });
        } elseif ($this->waitingList->flags_check == WaitingList::ANY_FLAGS && $this->flags->count() > 0) {
            return $this->flags->some(function ($model) {
                return $model->pivot->value;
            });
        }

        return true;
    }

    public function getEligibilityAttribute()
    {
        // is the status of the account deferred
        // are all the flags true
        // and is the atc hour check true
        return $this->atcHourCheck() && $this->allFlagsChecker() && $this->current_status->name == 'Active';
    }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = (string) $value;
    }

    private function cacheKey()
    {
        return "waiting-list-account:{$this->id}";
    }
}
