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

        \App\Events\VisitTransferLegacy\ApplicationSubmitted::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransferLegacy\NotifyAllReferees::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationUnderReview::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransferLegacy\NotifyCommunityOfUnderReviewApplication::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationRejected::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationAccepted::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
            \App\Listeners\VisitTransferLegacy\NotifyTrainingDepartmentOfAcceptedApplication::class,
            \App\Listeners\VisitTransferLegacy\SyncVisitingControllerToCts::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationCompleted::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationCancelled::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationWithdrawn::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransferLegacy\ApplicationStatusChanged::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfStatusChange::class,
        ],

        \App\Events\VisitTransferLegacy\ReferenceCancelled::class => [
            \App\Listeners\VisitTransferLegacy\NotifyRefereeOfReferenceCancellation::class,
        ],

        \App\Events\VisitTransferLegacy\ReferenceUnderReview::class => [
            \App\Listeners\VisitTransferLegacy\NotifyRefereeOfReferenceCompletion::class,
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfReferenceCompletion::class,
        ],

        \App\Events\VisitTransferLegacy\ReferenceAccepted::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfReferenceAcceptance::class,
        ],

        \App\Events\VisitTransferLegacy\ReferenceRejected::class => [
            \App\Listeners\VisitTransferLegacy\NotifyApplicantOfReferenceRejection::class,
        ],

        \App\Events\VisitTransferLegacy\ReferenceDeleted::class => [
            \App\Listeners\VisitTransferLegacy\NotifyRefereeOfReferenceDeletion::class,
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
        \App\Events\Mship\Endorsement\TierEndorsementAdded::class => [
            \App\Listeners\Mship\Endorsement\NotifyOfTierEndorsement::class,
        ],
        \App\Events\Mship\Endorsement\PositionEndorsementAdded::class => [
            \App\Listeners\Mship\Endorsement\NotifyOfPositionEndorsement::class,
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
