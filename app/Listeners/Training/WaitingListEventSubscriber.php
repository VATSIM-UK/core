<?php

namespace App\Listeners\Training;

use Illuminate\Support\Facades\Log;

class WaitingListEventSubscriber
{
    public function accountAdded($event)
    {
        return Log::stack(['training'], "Account {$event->account} ({$event->account->id}) was added to {$event->waitingList}");
    }

    public function accountPromoted()
    {
        // TODO: Implement proper logging.
    }

    public function accountDemoted()
    {
        // TODO: Implement proper logging.
    }

    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Training\AccountAddedToWaitingList',
            'App\Listeners\WaitingListEventSubscriber@accountAdded'
        );

        $events->listen(
            'App\Events\Training\AccountPromotedInWaitingList',
            'App\Listeners\WaitingListEventSubscriber@accountPromoted'
        );

        $events->listen(
            'App\Events\Training\AccountDemotedInWaitingList',
            'App\Listeners\WaitingListEventSubscriber@accountDemoted'
        );
    }
}
