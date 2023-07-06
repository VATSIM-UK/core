<?php

namespace App\Listeners\Training\WaitingList;

use App\Contracts\AccountCentricEvent;
use App\Jobs\Training\CheckAccountWaitingListEligibility;
use Illuminate\Support\Facades\Log;

class CheckAccountWaitingListEligibilityListener
{
    /**
     * Handle the event.
     *
     * @param  \App\Contracts\AccountCentricEvent  $event
     * @return void
     */
    public function handle(AccountCentricEvent $event)
    {
        $eventName = get_class($event);
        Log::debug("Dispatching CheckAccountWaitingListEligibility job from listener on event {$eventName} for account {$event->getAccount()->id}");

        CheckAccountWaitingListEligibility::dispatch($event->getAccount());
    }
}
