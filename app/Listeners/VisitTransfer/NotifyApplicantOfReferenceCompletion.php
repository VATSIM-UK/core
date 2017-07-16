<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceUnderReview;
use App\Notifications\ApplicationReferenceSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

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
