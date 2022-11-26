<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountWithinFiveDaysOfWaitingListRemoval;
use App\Notifications\Training\WaitingListRemovalReminder;

class SendWaitingListRemovalFiveDayReminderNotification
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
     * @param  object  $event
     * @return void
     */
    public function handle(AccountWithinFiveDaysOfWaitingListRemoval $event)
    {
        $event->account->notify(new WaitingListRemovalReminder($event->waitingList->name, $event->removalDate));
    }
}