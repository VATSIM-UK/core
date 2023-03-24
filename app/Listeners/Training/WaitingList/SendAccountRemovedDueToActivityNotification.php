<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountRemovedFromWaitingListDueToActivity;
use App\Notifications\Training\WaitingListRemovalCompleted;

class SendAccountRemovedDueToActivityNotification
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(AccountRemovedFromWaitingListDueToActivity $event)
    {
        $event->account->notify(new WaitingListRemovalCompleted($event->waitingList->name));
    }
}
