<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceDeleted;
use App\Notifications\ApplicationReferenceCancelled;

class NotifyRefereeOfReferenceDeletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceDeleted $event)
    {
        $event->reference->notify(new ApplicationReferenceCancelled($event->reference));
    }
}
