<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountRemovedFromWaitingList;
use Illuminate\Support\Facades\Log;

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
        Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was removed from {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }
}
