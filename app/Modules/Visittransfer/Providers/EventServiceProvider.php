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
        'App\Modules\Visittransfer\Events\ApplicationAccepted' => [

        ],

        'App\Modules\Visittransfer\Events\ApplicationCreated' => [

        ],

        'App\Modules\Visittransfer\Events\ApplicationRejected' => [

        ],

        'App\Modules\Visittransfer\Events\ApplicationSubmitted' => [
            \App\Modules\Visittransfer\Listeners\SendSubmissionConfirmationToApplicant::class,
            \App\Modules\Visittransfer\Listeners\InitialContactOfAllReferees::class,
        ],

        'App\Modules\Visittransfer\Events\ApplicationUpdated' => [

        ],

        'App\Modules\Visittransfer\Events\ReferenceUnderReview' => [
            '\App\Modules\Visittransfer\Listeners\NotifyRefereeOnReferenceCompletion',
            '\App\Modules\Visittransfer\Listeners\NotifyApplicantOnReferenceCompletion',
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
