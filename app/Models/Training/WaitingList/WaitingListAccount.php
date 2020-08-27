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

    public function addFlag(WaitingListFlag $listFlag, $value = null)
    {
        return $this->flags()->attach($listFlag, ['marked_at' => $value]);
    }

    /**
     * Mark a Flag as true.
     *
     * @param  WaitingListFlag  $listFlag
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

    public function atcHourCheck()
    {
        if ($this->waitingList->department === WaitingList::PILOT_DEPARTMENT) {
            return true;
        }
        $hourCheckKey = "{$this->cacheKey()}:atcHourCheck";

        if ((bool) Cache::has($hourCheckKey)) {
            return (bool) Cache::get($hourCheckKey);
        }

        // gather the sessions from the last 3 months in the UK (isUK scope)
        $hours = Atc::where('account_id', $this->account_id)
            ->whereDate('disconnected_at', '>=', Carbon::parse('3 months ago'))->isUk()->sum('minutes_online');

        // 12 hours is represented as 720 minutes
        $minutesRequired = 720;
        // for a user in a waiting list, they should have > 12 hours controlled within the UK.
        if ($hours >= $minutesRequired) {
            Cache::put($hourCheckKey, true, $this->cacheTtl);

            return true;
        }

        Cache::put($hourCheckKey, false, $this->cacheTtl);

        return false;
    }

    public function getAtcHourCheckAttribute()
    {
        return $this->atcHourCheck();
    }

    public function allFlagsChecker()
    {
        $checked = true;

        // iterate through each of the flags to see if they are true. If a false flag is detected, stop iterating.
        $this->flags()->each(function ($model) use (&$checked) {
            if (! $model->pivot->value) {
                $checked = false;

                return false;
            }
        });

        return $checked;
    }

    public function getEligibilityAttribute()
    {
        // is the status of the account deferred
        // are all the flags true
        // and is the atc hour check true
        return $this->atcHourCheck() && $this->allFlagsChecker() && $this->status->first()->name == 'Active';
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
