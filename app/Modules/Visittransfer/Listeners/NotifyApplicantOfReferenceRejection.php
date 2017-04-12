<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceRejected;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRejected;

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
