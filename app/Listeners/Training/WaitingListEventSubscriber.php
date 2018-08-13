<?php

namespace App\Listeners\Training;

use Illuminate\Support\Facades\Log;

class WaitingListEventSubscriber
{
    public function accountAdded($event)
    {
        return Log::channel('stack')->info("Account {$event->account} ({$event->account->id}) was added to {$event->waitingList}");
    }

    public function accountPromoted($event)
    {
        return Log::channel('stack')->info("Account {$event->account} ({$event->account->id}) was promoted within {$event->waitingList}");
    }

    public function accountDemoted($event)
    {
        return Log::channel('stack')->info("Account {$event->account} ({$event->account->id}) was demoted within {$event->waitingList}");
    }

    public function accountRemoved($event)
    {
        return Log::channel('stack')->info("Account {$event->account} ({$event->account->id}) was removed from {$event->waitingList}");
    }

    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Training\AccountAddedToWaitingList',
            'App\Listeners\Training\WaitingListEventSubscriber@accountAdded'
        );

        $events->listen(
            'App\Events\Training\AccountPromotedInWaitingList',
            'App\Listeners\Training\WaitingListEventSubscriber@accountPromoted'
        );

        $events->listen(
            'App\Events\Training\AccountDemotedInWaitingList',
            'App\Listeners\Training\WaitingListEventSubscriber@accountDemoted'
        );

        $events->listen(
            'App\Events\Training\AccountRemovedFromWaitingList',
            'App\Listeners\Training\WaitingListEventSubscriber@accountRemoved'
        );
    }
}
