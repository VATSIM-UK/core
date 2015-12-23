<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Mship\AccountTouched' => [
            'App\Listeners\Sync\PushToForum',
            'App\Listeners\Sync\PushToMoodle',
            'App\Listeners\Sync\PushToRts',
            'App\Listeners\Sync\PushToPts',
            'App\Listeners\Sync\PushToTeamspeak',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
