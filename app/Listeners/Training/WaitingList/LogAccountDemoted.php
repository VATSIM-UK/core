<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountDemotedInWaitingList;
use Illuminate\Support\Facades\Log;

class LogAccountDemoted
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
    public function handle(AccountDemotedInWaitingList $event)
    {
        Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was demoted within {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }
}
