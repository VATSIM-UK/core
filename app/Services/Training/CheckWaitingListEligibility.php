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
    ) {
    }

    private ?bool $baseControllingHoursCheck = null;

    private ?bool $waitingListFlagsCheck = null;

    public function checkBaseControllingHours(WaitingList $waitingList)
    {
        if (! $waitingList->should_check_atc_hours || $waitingList->department == WaitingList::PILOT_DEPARTMENT) {
            $this->baseControllingHoursCheck = true;

            return true;
        }

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
            return ['overall' => true, 'summary' => null];
        }

        $summaryByFlag = $waitingListAccount->flags()->get()->mapWithKeys(function ($flag) {
            return [$flag->name => $flag->pivot->value];
        });

        $method = $waitingList->flags_check == WaitingList::ALL_FLAGS ? 'every' : 'some';
        // check if all flags are true or if any flags are true depending on the waiting list flags check type
        $this->waitingListFlagsCheck = $summaryByFlag->$method(fn ($value) => $value);

        return ['overall' => $this->waitingListFlagsCheck, 'summary' => $summaryByFlag->toArray()];
    }

    public function checkAccountStatus(WaitingList $waitingList)
    {
        $waitingListAccount = $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;

        return $waitingListAccount->current_status->name == 'Active';
    }

    public function getOverallEligibility(WaitingList $waitingList): bool
    {
        $base_hour_checks = $this->checkBaseControllingHours($waitingList);
        $flags_summary = $this->checkWaitingListFlags($waitingList);

        return $base_hour_checks && $flags_summary['overall'] && $this->checkAccountStatus($waitingList);
    }

    public function getWaitingListAccount(WaitingList $waitingList)
    {
        return $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;
    }
}
