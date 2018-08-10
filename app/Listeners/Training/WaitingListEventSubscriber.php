<?php

namespace App\Listeners\Training;

use Illuminate\Support\Facades\Log;

class WaitingListEventSubscriber
{
    public function userAdded($event)
    {
        return Log::stack(['training'], "Account {$event->account} ({$event->account->id}) was added to {$event->waitingList}");
    }

    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Training\AccountAddedToWaitingList',
            'App\Listeners\WaitingListEventSubscriber@userAdded'
        );
    }
}