<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountRemovedFromWaitingList;

class LogAccountRemoved
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountRemovedFromWaitingList $event)
    {
        audit('Account removed from waiting list', [
            'account_id' => $event->account->id,
            'waiting_list_id' => $event->waitingList->id,
            'staff_id' => $event->staffAccount->id,
        ]);
    }
}
