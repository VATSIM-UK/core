<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountAddedToWaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;

class AssignDefaultStatus
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
     * @param  AccountAddedToWaitingList $event
     * @return void
     */
    public function handle(AccountAddedToWaitingList $event)
    {
        // to cover old records e.g. if user was re-added to list, position will always be greater than zero.

        $event->waitingList->accounts->where('id', $event->account->id)->where('pivot.position', '>', 0)->first()->pivot->addStatus(WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS));
    }
}
