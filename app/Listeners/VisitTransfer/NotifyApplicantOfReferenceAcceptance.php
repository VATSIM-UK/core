<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceAccepted;
use App\Notifications\ApplicationReferenceAccepted;

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
