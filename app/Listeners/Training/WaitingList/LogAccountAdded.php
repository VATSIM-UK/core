<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountAddedToWaitingList;

class LogAccountAdded
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
    public function handle(AccountAddedToWaitingList $event)
    {
        audit('Account added to waiting list', [
            'account_id' => $event->account->id,
            'waiting_list_id' => $event->waitingList->id,
            'staff_id' => $event->staffAccount->id,
        ]);
    }
}
