<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
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
        $account = $event->account->refresh();

        if (! $account->is_inactive) {
            Log::debug("Account {$account->id} is not inactive, skipping");

            return;
        }

        if ($account->currentWaitingLists->count() == 0) {
            Log::debug("Inactive account {$account->id} is not in a waiting list, skipping");

            return;
        }

        foreach ($account->currentWaitingLists as $waitingList) {
            Log::info("Inactive account {$account->id} is in waiting list {$waitingList->id} - removing from waiting list");

            $waitingList->removeFromWaitingList($account);
        }

        Log::info("Account {$account->id} is in waiting lists {$account->currentWaitingLists->pluck('id')->join(', ')}, with inactive account state - (fake) notifying account");

        $account->notify(new RemovedFromWaitingListInactiveAccount($account->currentWaitingLists));
    }
}
