<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceUnderReview;
use App\Notifications\ApplicationReferenceSubmitted;

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
