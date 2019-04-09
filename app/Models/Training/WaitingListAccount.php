<?php

namespace App\Models\Training;

use App\Models\NetworkData\Atc;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccount extends Pivot
{
    use SoftDeletes;

    public $table = 'training_waiting_list_account';

    public $fillable = ['position', 'added_by', 'deleted_at'];

    protected $appends = ['atcHourCheck'];

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
        )->withPivot(['marked_at'])->using(WaitingListAccountFlag::class);
    }

    /**
     * @param \App\Models\Training\WaitingListStatus $listStatus
     */
    public function addStatus(WaitingListStatus $listStatus)
    {
        $nonEnded = $this->status->reject(function ($value, $key) {
            return !is_null($value->pivot->end_at);
        });

        $nonEnded->each(function ($item, $key) {
            $item->pivot->endStatus();
        });

        return $this->status()->attach($listStatus, ['start_at' => now()]);
    }

    /**
     * @param \App\Models\Training\WaitingListStatus $listStatus
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
     * @param WaitingListFlag $listFlag
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

        if (!$flag->value) {
            return;
        }

        $flag->unMark();
    }

    public function setPositionAttribute($value)
    {
        $this->attributes['position'] = (int) $value;
    }

    public function decrementPosition($value = 1)
    {
        $this->position -= $value;
        $this->save();

        return $this->position;
    }

    public function incrementPosition($value = 1)
    {
        $this->position += $value;
        $this->save();

        return $this->position;
    }

    public function atcHourCheck()
    {
        // for a user in a waiting list, they should have > 12 hours controlled within the UK.
        $controllingSessions = Atc::whereAccountId($this->account_id)
            ->whereBetween('disconnected_at', [\Carbon\Carbon::parse('3 months ago'), now()])->isUk();

        $time = $controllingSessions->sum('minutes_online');

        if ($time >= 720) {
            return true;
        }

        return false;
    }

    public function getAtcHourCheckAttribute()
    {
        return $this->atcHourCheck();
    }
}
