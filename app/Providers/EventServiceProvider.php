<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Mship\AccountTouched::class => [
            \App\Listeners\Sync\PushToForum::class,
            \App\Listeners\Sync\PushToMoodle::class,
            \App\Listeners\Sync\PushToRts::class,
            \App\Listeners\Sync\PushToPts::class,
            \App\Listeners\Sync\PushToTeamSpeak::class,
        ],
        \App\Events\Mship\Feedback\NewFeedbackEvent::class => [
            \App\Listeners\Mship\Feedback\NotifyOfNewFeedback::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
