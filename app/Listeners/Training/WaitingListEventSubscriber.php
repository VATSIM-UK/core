<?php

namespace App\Listeners\Training;

use Illuminate\Support\Facades\Log;

class WaitingListEventSubscriber
{
    public function accountAdded($event)
    {
        return Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was added to {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }

    public function accountPromoted($event)
    {
        return Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was promoted within {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }

    public function accountDemoted($event)
    {
        return Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was demoted within {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }

    public function accountRemoved($event)
    {
        return Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) was removed from {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
    }

    public function accountStatusChange($event)
    {
        return Log::channel('training')
            ->info("Account {$event->account} ({$event->account->id}) has their status changed in {$event->waitingList} by {$event->staffAccount} ({$event->staffAccount->id})");
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

        $event->listen(
            'App\Events\Training\AccountChangedStatusInWaitingList',
            'App\Listeners\Training\WaitingListEventSubscriber@accountStatusChange'
        );
    }
}
