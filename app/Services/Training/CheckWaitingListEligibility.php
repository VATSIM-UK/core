<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use Carbon\Carbon;

class CheckWaitingListEligibility
{
    public function __construct(
        private Account $account
    ) {}

    private ?bool $baseControllingHoursCheck = null;
    private ?bool $waitingListFlagsCheck = null;

    public function checkBaseControllingHours()
    {
        // avoid extra queries if method called multiple times
        // for lifecycle of class.
        if ($this->baseControllingHoursCheck !== null) {
            return $this->baseControllingHoursCheck;
        }

        $recentAtcMinutes = Atc::where('account_id', $this->account->id)
            ->where('disconnected_at', '>=', Carbon::parse('3 months ago'))->isUk()
            ->sum('minutes_online');

        $this->baseControllingHoursCheck = $recentAtcMinutes >= 720;

        return $this->baseControllingHoursCheck;
    }

    public function checkWaitingListFlags(WaitingList $waitingList)
    {
        $waitingListAccount = $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;

        if ($waitingList->flags->count() == 0) {
            return [true, null];
        }

        $summaryByFlag = $waitingListAccount->flags()->get()->mapWithKeys(function ($flag) {
            return [$flag->id => $flag->pivot->value];
        });

        $method = $waitingList->flags_check == WaitingList::ALL_FLAGS ? 'every' : 'some';
        // check if all flags are true or if any flags are true depending on the waiting list flags check type
        $this->waitingListFlagsCheck = $summaryByFlag->$method(fn ($value) => $value);

        return [$this->waitingListFlagsCheck, $summaryByFlag->toArray()];
    }
}
