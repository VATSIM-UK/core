<?php

namespace App\Modules\Networkdata\Providers;

use App\Modules\NetworkData\Events\AtcSessionEnded;
use App\Modules\NetworkData\Listeners\AtcSessionRecordedSuccessNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AtcSessionEnded::class => [
            //AtcSessionRecordedSuccessNotification::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
