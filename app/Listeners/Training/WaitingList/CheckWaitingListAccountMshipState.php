<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Support\Facades\Log;

class CheckWaitingListAccountMshipState
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Mship\AccountAltered  $event
     * @return void
     */
    public function handle(AccountAltered $event)
    {
        Log::debug("CheckWaitingListAccountMshipState listener triggered for account {$event->account->id}");
        $account = $event->account;

        $accountsWaitingList = $account->currentWaitingLists;

        if ($account->hasState('DIVISION')) {
            Log::debug("Account {$account->id} has DIVISION state, skipping removal from waiting list");

            return;
        }

        if ($accountsWaitingList->count() == 0) {
            Log::debug("Account {$account->id} is not waiting list, skipping");

            return;
        }

        foreach ($accountsWaitingList as $waitingList) {
            Log::info("Account {$account->id} is in waiting list {$waitingList->id}, with non-home member state - removing from waiting list");
            $waitingList->removeFromWaitingList($account);
        }

        $account->notify(new RemovedFromWaitingListNonHomeMember);
    }
}
