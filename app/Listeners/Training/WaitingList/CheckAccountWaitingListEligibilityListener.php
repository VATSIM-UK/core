<?php

namespace App\Listeners\Training\WaitingList;

use App\Contracts\AccountCentricEvent;
use App\Jobs\Training\UpdateAccountWaitingListEligibility;
use Illuminate\Support\Facades\Log;

class CheckAccountWaitingListEligibilityListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountCentricEvent $event)
    {
        $eventName = get_class($event);
        Log::debug("Dispatching CheckAccountWaitingListEligibilityListener job from listener on event {$eventName} for account {$event->getAccount()->id}");

        UpdateAccountWaitingListEligibility::dispatch($event->getAccount());
    }
}
