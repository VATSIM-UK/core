<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountRemovedFromWaitingList;

class EndAssignedStatus
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
     * @param  AccountRemovedFromWaitingList  $event
     * @return void
     */
    public function handle(AccountRemovedFromWaitingList $event)
    {
        //
    }
}
