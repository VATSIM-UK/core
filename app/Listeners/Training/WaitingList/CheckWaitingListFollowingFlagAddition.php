<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\FlagAddedToWaitingList;
use Illuminate\Support\Facades\Artisan;

class CheckWaitingListFollowingFlagAddition
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(FlagAddedToWaitingList $event)
    {
        Artisan::call('waiting-lists:check-eligibility', [
            '--waiting-list' => $event->getWaitingList()->id,
        ]);
    }
}
