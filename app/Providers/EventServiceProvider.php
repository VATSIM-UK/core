<?php

namespace App\Providers;

use App\Events\NetworkData\AtcSessionEnded;
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
            // Look to implement a sync to external services here
        ],

        \App\Events\Mship\QualificationAdded::class => [
            \App\Listeners\Mship\SendS1Email::class,
        ],

        \App\Events\Mship\Feedback\NewFeedbackEvent::class => [
            //\App\Listeners\Mship\Feedback\NotifyOfNewFeedback::class,
        ],

        \App\Events\Mship\Bans\BanUpdated::class => [
            \App\Listeners\Sync\Bans\SyncBanToTs::class,
            \App\Listeners\Sync\Bans\SyncBanToForum::class,
        ],

        AtcSessionEnded::class => [
            //AtcSessionRecordedSuccessNotification::class, // temporarily disabled
        ],

        \App\Events\VisitTransfer\ApplicationSubmitted::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransfer\NotifyAllReferees::class,
        ],

        \App\Events\VisitTransfer\ApplicationUnderReview::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransfer\NotifyCommunityOfUnderReviewApplication::class,
        ],

        \App\Events\VisitTransfer\ApplicationRejected::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ApplicationAccepted::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransfer\NotifyTrainingDepartmentOfAcceptedApplication::class,
        ],

        \App\Events\VisitTransfer\ApplicationCompleted::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ApplicationStatusChanged::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ReferenceCancelled::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfReferenceCancellation::class,
        ],

        \App\Events\VisitTransfer\ReferenceUnderReview::class => [
            \App\Listeners\VisitTransfer\NotifyRefereeOfReferenceCompletion::class,
            \App\Listeners\VisitTransfer\NotifyApplicantOfReferenceCompletion::class,
        ],

        \App\Events\VisitTransfer\ReferenceAccepted::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfReferenceAcceptance::class,
        ],

        \App\Events\VisitTransfer\ReferenceRejected::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfReferenceRejection::class,
        ],

        \App\Events\VisitTransfer\ReferenceDeleted::class => [
            \App\Listeners\VisitTransfer\NotifyRefereeOfReferenceDeletion::class,
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
