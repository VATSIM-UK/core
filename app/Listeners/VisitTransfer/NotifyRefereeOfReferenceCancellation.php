<?php

namespace App\Listeners\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ReferenceCancelled;
use App\Notifications\ApplicationReferenceNoLongerNeeded;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRefereeOfReferenceCancellation implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceCancelled $event)
    {
        $event->reference->notify(new ApplicationReferenceNoLongerNeeded($event->reference));
    }
}
