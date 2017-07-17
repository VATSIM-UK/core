<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceUnderReview;
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
