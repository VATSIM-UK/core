<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;

trait HasWaitingLists
{
    public function waitingLists()
    {
        return $this->belongsToMany(
            WaitingList::class,
            'training_waiting_list_account',
            'account_id',
            'list_id'
        )->using(WaitingListAccount::class)->withPivot(['id', 'deleted_at']);
    }

    public function currentWaitingLists()
    {
        return $this->waitingLists()->wherePivot('deleted_at', null);
    }
}
