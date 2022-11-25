<?php

namespace App\Providers;

use App\Events\Discord\DiscordLinked;
use App\Events\Discord\DiscordUnlinked;
use App\Events\NetworkData\AtcSessionEnded;
use App\Events\Smartcars\BidCompleted;
use App\Listeners\Discord\RemoveDiscordUser;
use App\Listeners\Discord\SetupDiscordUser;
use App\Listeners\NetworkData\FlushEndorsementCache;
use App\Listeners\Smartcars\EvaluateFlightCriteria;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Mship\AccountAltered::class => [
            \App\Listeners\Training\WaitingList\CheckWaitingListAccountMshipState::class,
        ],

        \App\Events\Mship\Qualifications\QualificationAdded::class => [
            \App\Listeners\Mship\SendS1Email::class,
        ],

        \App\Events\Mship\Bans\BanUpdated::class => [
            \App\Listeners\Sync\Bans\SyncBanToTs::class,
            \App\Listeners\Sync\Bans\SyncBanToForum::class,
        ],

        \App\Events\Mship\Feedback\NewFeedbackEvent::class => [
            //\App\Listeners\Mship\Feedback\NotifyOfNewFeedback::class,
        ],

        AtcSessionEnded::class => [
            //AtcSessionRecordedSuccessNotification::class, // temporarily disabled
            FlushEndorsementCache::class,
        ],

        \App\Events\Training\WaitingListAtcPositionsChanged::class => [
            \App\Listeners\Training\WaitingList\SendTopTenAtcNotifications::class,
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
            \App\Listeners\VisitTransfer\SyncVisitingControllerToCts::class,
        ],

        \App\Events\VisitTransfer\ApplicationCompleted::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ApplicationCancelled::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ApplicationWithdrawn::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ApplicationStatusChanged::class => [
            \App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransfer\ReferenceCancelled::class => [
            \App\Listeners\VisitTransfer\NotifyRefereeOfReferenceCancellation::class,
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

        BidCompleted::class => [
            EvaluateFlightCriteria::class,
        ],

        DiscordLinked::class => [
            SetupDiscordUser::class,
        ],

        DiscordUnlinked::class => [
            RemoveDiscordUser::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\Mship\SyncSubscriber',
    ];
}
