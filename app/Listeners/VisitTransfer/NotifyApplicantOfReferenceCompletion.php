<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceUnderReview;
use App\Notifications\ApplicationReferenceSubmitted;

class NotifyApplicantOfReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicationReferenceSubmitted($reference));
    }
}
