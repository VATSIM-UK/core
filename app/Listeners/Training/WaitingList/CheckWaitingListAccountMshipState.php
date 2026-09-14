<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Support\Facades\Log;

class CheckWaitingListAccountMshipState
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountAltered $event)
    {
        Log::debug('CheckWaitingListAccountMshipState listener triggered for account', ['account_id' => $event->account->id]);
        // ensure we have the latest data
        $account = $event->account->refresh();

        $accountsWaitingList = $account->currentWaitingLists()->filter(function (WaitingList $waitingList) {
            return $waitingList->home_members_only;
        });

        if ($account->hasState(State::findByCode('DIVISION'))) {
            Log::debug('Account has DIVISION state, skipping removal from waiting list', ['account_id' => $account->id]);

            return;
        }

        if ($accountsWaitingList->count() == 0) {
            Log::debug("Account is not in a 'home members only' waiting list, skipping", ['account_id' => $account->id]);

            return;
        }

        foreach ($accountsWaitingList as $waitingList) {
            Log::info('Account is in waiting list with non-home member state - removing from waiting list', [
                'account_id' => $account->id,
                'waiting_list_id' => $waitingList->id,
            ]);

            $waitingList->removeFromWaitingList($account, new WaitingList\Removal(WaitingList\RemovalReason::NonHome, null));
        }

        Log::info('Account is in waiting lists with non-home member state - notifying account', [
            'account_id' => $account->id,
            'waiting_list_ids' => $accountsWaitingList->pluck('id')->join(', '),
        ]);

        $account->notify(new RemovedFromWaitingListNonHomeMember($accountsWaitingList));
    }
}
