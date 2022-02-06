<?php

namespace App\Models\Mship\Concerns;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;

trait HasWaitingListPositions
{
    public function waitingLists()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_account'
        )->using(WaitingListAccount::class)->wherePivot('deleted_at', null);
    }
}
