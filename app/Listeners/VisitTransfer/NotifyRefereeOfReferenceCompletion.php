<?php

namespace App\Listeners\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ReferenceUnderReview;
use App\Notifications\ApplicationReferenceSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRefereeOfReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $event->reference->notify(new ApplicationReferenceSubmitted($event->reference));
    }
}
