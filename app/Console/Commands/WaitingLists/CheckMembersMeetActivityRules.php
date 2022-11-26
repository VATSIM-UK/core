<?php

namespace App\Console\Commands\WaitingLists;

use App\Console\Commands\Command;
use App\Events\Training\AccountMarkedForRemovalFromWaitingList;
use App\Events\Training\AccountRegainedActivityRequirementsForWaitingList;
use App\Events\Training\AccountRemovedFromWaitingListDueToActivity;
use App\Events\Training\AccountWithinFiveDaysOfWaitingListRemoval;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use Carbon\Carbon;

class CheckMembersMeetActivityRules extends Command
{
    protected $minutesRequired = 720; // 12 hours is represented as 720 minutes

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'waitinglists:hourchecker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks ATC hour totals for members who are on an ATC waiting list and marks inactive members for automatic removal from the list.';

    /**
     * Executes all necessary console commands.
     */
    public function handle()
    {
        foreach ($this->retrieveHourEnforcedWaitingLists() as $waitingList) {
            $this->identifyNewNonEligibleWaitingListAccounts($waitingList);
            $this->processWaitingListAccountsMarkedForRemoval($waitingList);
        }
    }

    /**
     * Retrieves training waiting lists which enforce hour requirements.
     */
    protected function retrieveHourEnforcedWaitingLists()
    {
        return WaitingList::all()->filter(function ($waitingList) {
            return $waitingList->isAtcList();
        });
    }

    /**
     * Identifies accounts on enforced waiting lists who have fallen below the hour requirement.
     *
     * @param  WaitingList  $waitingList
     */
    protected function identifyNewNonEligibleWaitingListAccounts(WaitingList $waitingList)
    {
        // We only want to find Active waiting list accounts which are not already marked for removal
        $waitingListAccounts = $waitingList->accounts
            ->filter(function ($account) {
                return is_null($account->pivot->pending_removal?->removal_date) || $account->pivot->pending_removal->status != 'Pending';
            })
            ->filter(function ($account) {
                return $account->pivot->current_status->name == 'Active';
            });

        foreach ($waitingListAccounts as $account) {
            // Calculate the total activity within the last 3 months
            $activeMinutes = Atc::where('account_id', $account->id)
                ->whereDate('disconnected_at', '>=', Carbon::parse('3 months ago'))
                ->isUk()
                ->sum('minutes_online');

            // When the hours are less than the requirement, set a removal date
            if ($activeMinutes < $this->minutesRequired) {
                $removalDate = Carbon::now();
                $removalDate->addDays(31); // When calculating date diff, this will be a difference of 30 days
                $account->pivot->addPendingRemoval($removalDate);
                event(new AccountMarkedForRemovalFromWaitingList($account, $waitingList, $removalDate));
            }
        }
    }

    /**
     * Performs two actions:.
     *
     * - Checks to see if members who are pending removal have now met the hour criteria
     * - Sends reminder emails as per policy schedule, and actions waiting list removal
     *
     * @param  WaitingList  $waitingList
     */
    protected function processWaitingListAccountsMarkedForRemoval(WaitingList $waitingList)
    {
        $waitingListAccounts = $waitingList->accounts
            ->filter(function ($account) {
                return $account->pivot->current_status->name == 'Active' && $account->pivot->pending_removal?->status == 'Pending';
            });

        foreach ($waitingListAccounts as $account) {
            // Calculate the total activity within the last 3 months and cancel removal if eligible
            $activeMinutes = Atc::where('account_id', $account->id)
                ->whereDate('disconnected_at', '>=', Carbon::parse('3 months ago'))
                ->isUk()
                ->sum('minutes_online');

            if ($activeMinutes >= $this->minutesRequired) {
                $account->pivot->pending_removal->cancelRemoval();
                event(new AccountRegainedActivityRequirementsForWaitingList($account, $waitingList));
                continue;
            }

            // Action waiting list removals that have passed the removal date
            if (Carbon::now() >= Carbon::parse($account->pivot->pending_removal->removal_date)) {
                $waitingList->removeFromWaitingList($account);
                $account->pivot->pending_removal->markComplete();
                event(new AccountRemovedFromWaitingListDueToActivity($account, $waitingList));
                continue;
            }

            // Action waiting list reminders that qualify per the reminder schedule
            if (
                Carbon::parse($account->pivot->pending_removal->removal_date)->subDays(6) <= Carbon::now() &&
                $account->pivot->pending_removal->emails_sent < 1
            ) {
                $account->pivot->pending_removal->incrementEmailCount();
                event(new AccountWithinFiveDaysOfWaitingListRemoval($account, $waitingList, Carbon::parse($account->pivot->pending_removal->removal_date)));
            }
        }
    }
}