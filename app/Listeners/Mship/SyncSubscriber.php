<?php

namespace App\Listeners\Mship;

class SyncSubscriber
{
    /**
     * Syncs to all services
     */
    public function syncToAllServices($event)
    {
        \App\Jobs\Mship\SyncToCTS::dispatch($event->account);
        \App\Jobs\Mship\SyncToHelpdesk::dispatch($event->account);
        \App\Jobs\Mship\SyncToMoodle::dispatch($event->account);
        \App\Jobs\Mship\SyncToForums::dispatch($event->account);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
                \App\Events\Mship\AccountAltered::class,
                '\App\Listeners\Mship\SyncSubscriber@syncToAllServices'
            );
    }
}
