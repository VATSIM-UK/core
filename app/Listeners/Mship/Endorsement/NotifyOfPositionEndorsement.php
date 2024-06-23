<?php

namespace App\Listeners\Mship\Endorsement;

use App\Events\Mship\Endorsement\PositionEndorsementAdded;
use App\Notifications\Mship\Endorsement\SoloEndorsementNotification;

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
