<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\DTO\WaitingListFlagSummaryResult;

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
        return $this->getFlagSummaryResult($waitingList)->toArray();
    }

    public function getFlagSummaryResult(WaitingList $waitingList): WaitingListFlagSummaryResult
    {
        if ($waitingList->flags()->doesntExist()) {
            return new WaitingListFlagSummaryResult(null);
        }

        $summaryByFlag = $this->getWaitingListAccount($waitingList)->flags()->get()->mapWithKeys(function ($flag) {
            return [$flag->name => $flag->pivot->value];
        });

        return new WaitingListFlagSummaryResult($summaryByFlag->toArray());
    }

    public function getWaitingListAccount(WaitingList $waitingList): WaitingListAccount
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $waitingList->findWaitingListAccount($this->account);
    }
}
