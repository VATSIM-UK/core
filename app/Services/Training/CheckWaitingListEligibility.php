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

    public function checkRoster(WaitingList $waitingList)
    {
        if (! $waitingList->should_check_roster || $waitingList->department == WaitingList::PILOT_DEPARTMENT) {
            return true;
        }

        return Roster::where('account_id', $this->account->id)->exists();
    }

    /**
     * Check the waiting list flags defined in the waiting list
     * and return an array with the overall eligibility and a summary of the flags.
     *
     * This can either be the manual or automated flags backed by an endorsement.
     * The 'overall' key represents the status of the flags based on the waiting list flags check type.
     *
     * Within the 'summary' key, the key is the flag name and the value is the flag value.
     */
    public function checkWaitingListFlags(WaitingList $waitingList): array
    {
        $waitingListAccount = $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;

        if ($waitingList->flags()->doesntExist()) {
            return ['overall' => true, 'summary' => null];
        }

        $summaryByFlag = $waitingListAccount->flags()->get()->mapWithKeys(function ($flag) {
            return [$flag->name => $flag->pivot->value];
        });

        $method = $waitingList->flags_check == WaitingList::ALL_FLAGS ? 'every' : 'some';
        // check if all flags are true or if any flags are true depending on the waiting list flags check type
        $overall = $summaryByFlag->$method(fn ($value) => $value);

        return ['overall' => $overall, 'summary' => $summaryByFlag->toArray()];
    }

    public function checkAccountStatus(WaitingList $waitingList)
    {
        $waitingListAccount = $this->getWaitingListAccount($waitingList);

        return $waitingListAccount->current_status->name == 'Active';
    }

    public function getOverallEligibility(WaitingList $waitingList): bool
    {
        $on_roster = $this->checkRoster($waitingList);
        $flags_summary = $this->checkWaitingListFlags($waitingList);

        return $on_roster && $flags_summary['overall'] && $this->checkAccountStatus($waitingList);
    }

    public function getWaitingListAccount(WaitingList $waitingList)
    {
        return $waitingList->accounts()->where('account_id', $this->account->id)->first()->pivot;
    }
}
