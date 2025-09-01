<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Collection;

class WaitingListSelfEnrolment
{
    /**
     * Check if an account can self-enrol on a waiting list.
     *
     * For rating checks, the VATSIM bitmask is used to determine
     * the hierarchy for each type of rating i.e. between ATC and Pilot ratings.
     */
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

        // Check if the waiting list is at capacity
        if ($waitingList->isAtCapacity()) {
            return false;
        }

        if ($waitingList->requires_roster_membership && ! $account->onRoster()) {
            return false;
        }

        $accountIsNotHomeMember = ! $account->hasState(State::findByCode('DIVISION'));
        if ($waitingList->home_members_only && $accountIsNotHomeMember) {
            return false;
        }

        if ($waitingList->self_enrolment_minimum_qualification_id) {
            $activeQualification = self::getActiveQualificationForAccountOnList(
                $account,
                $waitingList
            );

            $minimumQualification = Qualification::find(
                $waitingList->self_enrolment_minimum_qualification_id
            );

            if ($minimumQualification && $activeQualification?->vatsim < $minimumQualification->vatsim) {
                return false;
            }
        }

        if ($waitingList->self_enrolment_maximum_qualification_id) {
            $activeQualification = self::getActiveQualificationForAccountOnList(
                $account,
                $waitingList
            );

            $maximumQualification = Qualification::find(
                $waitingList->self_enrolment_maximum_qualification_id
            );

            if ($maximumQualification && $activeQualification?->vatsim > $maximumQualification->vatsim) {
                return false;
            }
        }

        if ($waitingList->self_enrolment_hours_at_qualification_id) {
            $requiredQualification = Qualification::find(
                $waitingList->self_enrolment_hours_at_qualification_id
            );

            $atcSessionsAtQualificationsHours = Atc::where('account_id', $account->id)
                ->where('qualification_id', $requiredQualification->id)
                ->sum('minutes_online') / 60;

            if ($atcSessionsAtQualificationsHours < $waitingList->self_enrolment_hours_at_qualification_minimum_hours) {
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

    private static function getActiveQualificationForAccountOnList(
        Account $account,
        WaitingList $waitingList
    ): ?Qualification {
        return $waitingList->department == WaitingList::ATC_DEPARTMENT
            ? $account->qualification_atc
            : $account->qualification_pilot;
    }
}
