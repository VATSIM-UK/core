<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;

class CheckWaitingListFlags
{
    public function __construct(
        private Account $account
    ) {}

    /**
     * Check the waiting list flags defined in the waiting list
     * and return an array with the summary of the flags.
     *
     * Within the 'summary' key, the key is the flag name and the value is the flag value.
     */
    public function checkWaitingListFlags(WaitingList $waitingList): array
    {
        $waitingListAccount = $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;

        if ($waitingList->flags()->doesntExist()) {
            return ['summary' => null];
        }

        $summaryByFlag = $waitingListAccount->flags()->get()->mapWithKeys(function ($flag) {
            return [$flag->name => $flag->pivot->value];
        });

        return ['summary' => $summaryByFlag->toArray()];
    }

    public function getWaitingListAccount(WaitingList $waitingList)
    {
        return $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;
    }
}
