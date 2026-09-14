<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
use Illuminate\Support\Facades\Log;

class CheckWaitingListAccountInactivity
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountAltered $event)
    {
        Log::debug('CheckWaitingListAccountInactivity listener triggered for account', ['account_id' => $event->account->id]);
        $account = $event->account->refresh();

        if (! $account->is_inactive) {
            Log::debug('Account is not inactive, skipping', ['account_id' => $account->id]);

            return;
        }

        if ($account->currentWaitingLists()->count() == 0) {
            Log::debug('Inactive account is not in a waiting list, skipping', ['account_id' => $account->id]);

            return;
        }

        foreach ($account->currentWaitingLists() as $waitingList) {
            Log::info('Inactive account is in waiting list - removing from waiting list', [
                'account_id' => $account->id,
                'waiting_list_id' => $waitingList->id,
            ]);

            $waitingList->removeFromWaitingList($account, new Removal(RemovalReason::Inactivity, null));
        }

        Log::info('Account is in waiting lists with inactive account state - (fake) notifying account', [
            'account_id' => $account->id,
            'waiting_list_ids' => $account->currentWaitingLists()->pluck('id')->join(', '),
        ]);

        $account->notify(new RemovedFromWaitingListInactiveAccount($account->currentWaitingLists()));
    }
}
