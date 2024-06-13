<?php

namespace App\Listeners\Mship\Endorsement;

use App\Events\Mship\Endorsement\PositionEndorsementAdded;
use App\Notifications\Mship\Endorsement\SoloEndorsementNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyOfPositionEndorsement
{
    /**
     * Handle the event.
     */
    public function handle(PositionEndorsementAdded $event): void
    {
        $event->getAccount()->notify(new SoloEndorsementNotification($event->getEndorsement()));
    }
}
