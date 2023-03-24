<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\State;
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
        // ensure we have the latest data
        $account = $event->account->refresh();

        $accountsWaitingList = $account->currentWaitingLists->filter(function($waitingList) {
            return $waitingList->home_members_only;
        });

        if ($account->hasState(State::findByCode('DIVISION'))) {
            Log::debug("Account {$account->id} has DIVISION state, skipping removal from waiting list");

            return;
        }

        if ($accountsWaitingList->count() == 0) {
            Log::debug("Account {$account->id} is not in a 'home members only' waiting list, skipping");

            return;
        }

        foreach ($accountsWaitingList as $waitingList) {
            Log::info("Account {$account->id} is in waiting list {$waitingList->id}, with non-home member state - removing from waiting list");

            if (! $event->dryRun) {
                $waitingList->removeFromWaitingList($account);
            }
        }

        Log::info("Account {$account->id} is in waiting lists {$accountsWaitingList->pluck('id')->join(', ')}, with non-home member state - notifying account");

        if (! $event->dryRun) {
            $account->notify(new RemovedFromWaitingListNonHomeMember);
        }
    }
}
