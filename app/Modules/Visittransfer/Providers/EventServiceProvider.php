<?php namespace App\Modules\Visittransfer\Providers;

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
        \App\Modules\Visittransfer\Events\ApplicationUnderReview::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
            \App\Modules\Visittransfer\Listeners\NotifyCommunityOfUnderReviewApplication::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationSubmitted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
            \App\Modules\Visittransfer\Listeners\NotifyAllReferees::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationStatusChanged::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
        ],

        \App\Modules\Visittransfer\Events\ReferenceUnderReview::class => [
            \App\Modules\Visittransfer\Listeners\NotifyRefereeOnReferenceCompletion::class,
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfReferenceCompletion::class,
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
