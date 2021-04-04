<?php

namespace App\Listeners\Mship;

use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToForums;
use App\Jobs\Mship\SyncToMoodle;
use App\Jobs\Mship\SyncToDiscord;
use App\Jobs\Mship\SyncToHelpdesk;
use Illuminate\Support\Facades\Log;
use App\Events\Mship\AccountAltered;
use App\Events\Mship\Roles\RoleRemoved;
use App\Events\Mship\Roles\RoleAssigned;

class SyncSubscriber
{
    /**
     * Syncs to all services.
     */
    public function syncToAllServices($event)
    {
        if ($event->account->fully_defined) {
            SyncToCTS::dispatch($event->account);
            SyncToHelpdesk::dispatch($event->account);
            SyncToMoodle::dispatch($event->account);
            // SyncToForums::dispatch($event->account); - Re-enable tests (Feat/Adm/AccountTest & Unit/Mship/Sync/AccountAltered)
        }

        if ($event->account->discord_id) {
            SyncToDiscord::dispatch($event->account);
        }

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
            AccountAltered::class,
            '\App\Listeners\Mship\SyncSubscriber@syncToAllServices'
        );

        $events->listen(
            RoleAssigned::class,
            '\App\Listeners\Mship\SyncSubscriber@syncToAllServices'
        );

        $events->listen(
            RoleRemoved::class,
            '\App\Listeners\Mship\SyncSubscriber@syncToAllServices'
        );
    }
}
