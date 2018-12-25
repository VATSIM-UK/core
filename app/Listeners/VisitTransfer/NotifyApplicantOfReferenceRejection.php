<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceRejected;
use App\Notifications\ApplicationReferenceRejected;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceRejection implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceRejected $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicationReferenceRejected($reference));
    }
}
