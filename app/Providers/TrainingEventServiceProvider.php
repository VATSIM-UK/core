<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class TrainingEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\Training\AccountAddedToWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountAdded::class,
            \App\Listeners\Training\WaitingList\AssignFlags::class,
        ],
        \App\Events\Training\AccountRemovedFromWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountRemoved::class,
        ],
        \App\Events\Training\AccountNoteChanged::class => [
            \App\Listeners\Training\WaitingList\LogNoteChanged::class,
        ],
        \App\Events\Training\FlagAddedToWaitingList::class => [
            \App\Listeners\Training\WaitingList\CheckWaitingListFollowingFlagAddition::class,
        ],
        \App\Events\Training\EndorsementRequestApproved::class => [
            \App\Listeners\Training\Endorsement\CreateEndorsementFromApproval::class,
        ],
        \App\Events\Training\EndorsementRequestCreated::class => [
            \App\Listeners\Training\Endorsement\NotifyEndorsementRequestCreated::class,
        ],
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
