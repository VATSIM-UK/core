<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceAccepted;
use App\Notifications\ApplicationReferenceAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceAcceptance implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceAccepted $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicationReferenceAccepted($reference));
    }
}
