<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceRejected;
use App\Notifications\ApplicationReferenceRejected;

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
