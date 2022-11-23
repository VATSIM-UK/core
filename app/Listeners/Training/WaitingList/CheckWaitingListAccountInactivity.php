<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckWaitingListAccountInactivity
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Mship\AccountAltered  $event
     * @return void
     */
    public function handle(AccountAltered $event)
    {
        Log::debug("CheckWaitingListAccountInactivity listener triggered for account {$event->account->id}");
        $account = $event->account;

        if (!$account->inactive) {
            Log::debug("Account {$account->id} is not inactive, skipping");
            return;
        }

        if ($account->currentWaitingLists->count() == 0) {
            Log::debug("Inactive account {$account->id} is not in a waiting list, skipping");
            return;
        }

        $accountsWaitingList = $account->currentWaitingLists;

        foreach ($accountsWaitingList as $waitingList) {
            Log::info("Account {$account->id} is in waiting list {$waitingList->id}, with inactive account state - removing from waiting list");
            $waitingList->removeFromWaitingList($account);
        }

        $account->notify(new RemovedFromWaitingListInactiveAccount);
    }
}
