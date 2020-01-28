<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class TrainingEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\Training\AccountAddedToWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountAdded::class,
            \App\Listeners\Training\WaitingList\AssignDefaultStatus::class,
            \App\Listeners\Training\WaitingList\AssignFlags::class,
        ],
        \App\Events\Training\AccountRemovedFromWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountRemoved::class,
        ],
        \App\Events\Training\AccountPromotedInWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountPromoted::class,
        ],
        \App\Events\Training\AccountDemotedInWaitingList::class => [
            \App\Listeners\Training\WaitingList\LogAccountDemoted::class,
        ],
        \App\Events\Training\AccountNoteChanged::class => [
            \App\Listeners\Training\WaitingList\LogNoteChanged::class,
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
