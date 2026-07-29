<?php

namespace App\Listeners\Training;

use Illuminate\Support\Facades\Log;

// FIXME (logging plan, out of scope): this subscriber is not registered anywhere
// (EventServiceProvider::$subscribe only wires SyncSubscriber), so it is dead code.
// As a result, the promoted/demoted/status-change waiting-list events below are
// currently NOT written to any audit log. Additionally, subscribe() below calls
// `$event->listen(...)` instead of `$events->listen(...)` for the status-change
// registration, a pre-existing bug that would break that registration even if this
// subscriber were wired up. Both issues are left untouched here - out of scope.
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
