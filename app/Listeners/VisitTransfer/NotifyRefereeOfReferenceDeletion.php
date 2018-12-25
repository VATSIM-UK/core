<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceDeleted;
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
