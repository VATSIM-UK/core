<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountRegainedActivityRequirementsForWaitingList;
use App\Notifications\Training\WaitingListRemovalCancelled;

class SendAccountRegainedActivityNotification
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
    public function handle(AccountRegainedActivityRequirementsForWaitingList $event)
    {
        $event->account->notify(new WaitingListRemovalCancelled($event->waitingList->name));
    }
}
