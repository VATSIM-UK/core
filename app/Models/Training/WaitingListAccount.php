<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccount extends Pivot
{
    use SoftDeletes;

    public $table = 'training_waiting_list_account';

    public $fillable = ['position', 'deleted_at'];

    public function status()
    {
        return $this->belongsToMany(WaitingListStatus::class,
            'training_waiting_list_account_status', 'waiting_list_account_id', 'status_id')
            ->withPivot(['start_at', 'end_at'])->using(WaitingListAccountStatus::class);
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
}
