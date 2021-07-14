<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ApplicationAccepted;

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
