<?php

namespace App\Console\Commands\WaitingLists;

use App\Console\Commands\Command;
use App\Events\Training\AccountMarkedForRemovalFromWaitingList;
use App\Events\Training\AccountRegainedActivityRequirementsForWaitingList;
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
    protected $name = 'waitinglists:checkmembersmeetactivityrules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks ATC waiting list activity rules are being met for each account';

    /**
     * Executes all necessary console commands.
     */
    public function handle()
    {
        WaitingList::all()->filter(function ($waitingList) {
            return $waitingList->isAtcList();
        })->each(function (WaitingList $waitingList) {
            $waitingList->accounts
            ->filter(function ($account) {
                return $account->pivot->current_status->name == 'Active';
            })->each(function ($account) use ($waitingList) {
                $activeMinutes = Atc::where('account_id', $account->id)
                    ->whereDate('disconnected_at', '>=', Carbon::parse('3 months ago'))
                    ->isUk()
                    ->sum('minutes_online');

                if ($activeMinutes < $this->minutesRequired) {
                    if (is_null($account->pivot->pending_removal?->removal_date) || $account->pivot->pending_removal->status != 'Pending') {
                        $removalDate = Carbon::now();
                        $removalDate->addDays(31); // When calculating date diff, this will be a difference of 30 days
                        $account->pivot->addPendingRemoval($removalDate);
                        event(new AccountMarkedForRemovalFromWaitingList($account, $waitingList, $removalDate));
                    }
                } else {
                    if ($account->pivot->pending_removal?->status == 'Pending') {
                        $account->pivot->pending_removal->cancelRemoval();
                        event(new AccountRegainedActivityRequirementsForWaitingList($account, $waitingList));
                    }
                }
            });
        });
    }
}
