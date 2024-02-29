<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountPromotedInWaitingList;
use Illuminate\Support\Facades\Log;

class LogAccountPromoted
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
    public function handle(AccountPromotedInWaitingList $event)
    {
        Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was promoted within {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }
}
