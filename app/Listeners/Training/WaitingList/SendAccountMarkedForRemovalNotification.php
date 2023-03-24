<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountMarkedForRemovalFromWaitingList;
use App\Notifications\Training\WaitingListRemovalAdded;

class SendAccountMarkedForRemovalNotification
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(AccountMarkedForRemovalFromWaitingList $event)
    {
        $event->account->notify(new WaitingListRemovalAdded($event->waitingList->name, $event->removalDate));
    }
}
