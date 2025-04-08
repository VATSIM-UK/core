<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Collection;

class WaitingListSelfEnrolment
{
    public static function canAccountEnrolOnList(
        Account $account,
        WaitingList $waitingList
    ): bool {
        if (! $waitingList->self_enrolment_enabled) {
            return false;
        }

        if ($waitingList->includesAccount($account)) {
            return false;
        }

        if ($waitingList->requires_roster_membership && ! $account->onRoster()) {
            return false;
        }

        $onlyAcceptsHomeMembers = $waitingList->home_members_only;
        $accountIsNotHomeMember = ! $account->hasState(State::findByCode('DIVISION'));
        if ($onlyAcceptsHomeMembers && $accountIsNotHomeMember) {
            return false;
        }

        if ($waitingList->self_enrolment_maximum_qualification_id) {
            $requiredQualification = Qualification::find(
                $waitingList->self_enrolment_maximum_qualification_id
            );

            if (! $account->hasQualification($requiredQualification)) {
                return false;
            }
        }

        return true;
    }

    public static function getListsAccountCanSelfEnrol(Account $account): Collection
    {
        return WaitingList::where('self_enrolment_enabled', true)
            ->get()
            ->filter(
                fn (WaitingList $waitingList) => self::canAccountEnrolOnList($account, $waitingList)
            );
    }
}
