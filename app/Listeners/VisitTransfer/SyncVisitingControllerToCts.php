<?php

namespace App\Listeners\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ApplicationAccepted;

class SyncVisitingControllerToCts
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ApplicationAccepted $event)
    {
        $event->application->account->syncToCTS();
    }
}
