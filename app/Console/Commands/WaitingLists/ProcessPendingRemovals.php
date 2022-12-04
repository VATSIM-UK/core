<?php

namespace App\Console\Commands\WaitingLists;

use App\Console\Commands\Command;
use App\Events\Training\AccountRemovedFromWaitingListDueToActivity;
use App\Events\Training\AccountWithinFiveDaysOfWaitingListRemoval;
use App\Models\Training\WaitingList;
use Carbon\Carbon;

class ProcessPendingRemovals extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'waitinglists:processpendingremovals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processing waiting list accounts marked for automatic removal';

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
            })->filter(function ($account) {
                return $account->pivot->pending_removal?->status == 'Pending';
            })->each(function ($account) use ($waitingList) {
                // Send 5 day reminders
                if (
                    Carbon::parse($account->pivot->pending_removal->removal_date)->subDays(6) <= Carbon::now() &&
                    $account->pivot->pending_removal->emails_sent < 1
                ) {
                    $account->pivot->pending_removal->incrementEmailCount();
                    event(new AccountWithinFiveDaysOfWaitingListRemoval($account, $waitingList, Carbon::parse($account->pivot->pending_removal->removal_date)));
                }

                // Remove accounts past removal date
                if (Carbon::now() >= Carbon::parse($account->pivot->pending_removal->removal_date)) {
                    $waitingList->removeFromWaitingList($account);
                    $account->pivot->pending_removal->markComplete();
                    event(new AccountRemovedFromWaitingListDueToActivity($account, $waitingList));
                }
            });
        });
    }
}
