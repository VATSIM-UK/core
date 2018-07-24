<?php

namespace App\Models\Training\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListAccount;
use App\Models\Training\WaitingListStatus;

trait HasWaitingList
{
    public function waitingList()
    {
        return $this->belongsToMany(WaitingList::class, 'training_waiting_list_account',
            'account_id', 'list_id')->using(WaitingListAccount::class);
    }

    public function addToWaitingList(WaitingList $waitingList)
    {
        return $this->waitingList()->attach($waitingList);
    }
}