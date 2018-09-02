<?php

namespace App\Models\Training\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListAccount;

trait HasWaitingList
{
    public function waitingList()
    {
        return $this->belongsToMany(WaitingList::class, 'training_waiting_list_account',
            'account_id', 'list_id')->using(WaitingListAccount::class)->withPivot(['id', 'position']);
    }
}
