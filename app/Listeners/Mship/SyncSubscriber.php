<?php

namespace App\Listeners\Mship;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSubscriber
{
    /**
     * Syncs to all services
     */
    public function syncToAllServices($event)
    {
        $ranRecently = !Cache::add('SYNCSUB_'.$event->account->id, '1', 3 / 60);

        if ($ranRecently || !$event->account->fully_defined) {
            // Prevent unnecessary executions
            return;
        }

        \App\Jobs\Mship\SyncToCTS::dispatch($event->account);
        \App\Jobs\Mship\SyncToHelpdesk::dispatch($event->account);
        \App\Jobs\Mship\SyncToMoodle::dispatch($event->account);
        // \App\Jobs\Mship\SyncToForums::dispatch($event->account); - Re-enable tests

        Log::debug($event->account->real_name.' ('.$event->account->id.') was queued to sync to external services');
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
