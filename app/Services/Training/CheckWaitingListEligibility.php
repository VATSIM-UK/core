<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use Carbon\Carbon;

class CheckWaitingListEligibility
{
    public function __construct(
        private Account $account
    ) {
    }

    public function getOverallEligibility(): bool
    {
        $accountOnRoster = Roster::where('account_id', $this->account->id)->first();

        if (!$accountOnRoster) {
            return false;
        }

        return true;
    }

    public function getWaitingListAccount(WaitingList $waitingList)
    {
        return $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;
    }
}
