<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ReferenceCancelled;
use App\Notifications\ApplicationReferenceCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceCancellation implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceCancelled $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicationReferenceCancelled($reference));
    }
}
