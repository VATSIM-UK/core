<?php

namespace App\Listeners\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ReferenceDeleted;
use App\Notifications\ApplicationReferenceNoLongerNeeded;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRefereeOfReferenceDeletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceDeleted $event)
    {
        $event->reference->notify(new ApplicationReferenceNoLongerNeeded($event->reference));
    }
}
