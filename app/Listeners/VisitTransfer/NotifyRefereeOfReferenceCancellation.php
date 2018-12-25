<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceCancelled;
use App\Notifications\ApplicationReferenceNoLongerNeeded;

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
