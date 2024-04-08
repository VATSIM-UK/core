<?php

namespace App\Listeners\Mship\Endorsement;

use App\Events\Mship\Endorsement\TierEndorsementAdded;
use App\Notifications\Mship\Endorsement\TierEndorsementNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyOfTierEndorsement implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(TierEndorsementAdded $event): void
    {
        $event->getAccount()->notify(new TierEndorsementNotification($event->getEndorsement()));
    }
}
