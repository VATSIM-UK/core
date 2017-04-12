<?php

namespace App\Modules\Visittransfer\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Modules\Visittransfer\Events\ApplicationSubmitted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
            \App\Modules\Visittransfer\Listeners\NotifyAllReferees::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationUnderReview::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
            \App\Modules\Visittransfer\Listeners\NotifyCommunityOfUnderReviewApplication::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationRejected::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationAccepted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
            \App\Modules\Visittransfer\Listeners\NotifyTrainingDepartmentOfAcceptedApplication::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationCompleted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
        ],

        \App\Modules\Visittransfer\Events\ApplicationStatusChanged::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfStatusChange::class,
        ],

        \App\Modules\Visittransfer\Events\ReferenceUnderReview::class => [
            \App\Modules\Visittransfer\Listeners\NotifyRefereeOfReferenceCompletion::class,
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfReferenceCompletion::class,
        ],

        \App\Modules\Visittransfer\Events\ReferenceAccepted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfReferenceAcceptance::class,
        ],

        \App\Modules\Visittransfer\Events\ReferenceRejected::class => [
            \App\Modules\Visittransfer\Listeners\NotifyApplicantOfReferenceRejection::class,
        ],

        \App\Modules\Visittransfer\Events\ReferenceDeleted::class => [
            \App\Modules\Visittransfer\Listeners\NotifyRefereeOfReferenceDeletion::class,
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
