<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountAddedToWaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListFlag;

class AssignFlags
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
        /** @var WaitingListAccount $waitingList */
        $waitingListAccount = $event->waitingList->accounts()->findOrFail($event->account->id)->pivot;

        $flags = $event->waitingList->flags();

        $flags->each(function (WaitingListFlag $flag) use ($waitingListAccount) {
            // if the default value is to be true, set the flag to be marked
            if ((bool) $flag->default_value) {
                $waitingListAccount->addFlag($flag, now());
            } else {
                $waitingListAccount->addFlag($flag);
            }
        });
    }
}
