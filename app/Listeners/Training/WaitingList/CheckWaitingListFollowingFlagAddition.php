<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\FlagAddedToWaitingList;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class CheckWaitingListFollowingFlagAddition
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Training\FlagAddedToWaitingList  $event
     * @return void
     */
    public function handle(FlagAddedToWaitingList $event)
    {
        Artisan::call('waiting-lists:check-eligibility', [
            '--waiting-list' => $event->getWaitingList()->id,
        ]);
    }
}
