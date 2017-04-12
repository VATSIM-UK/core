<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Notifications\ApplicationReferenceAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceAccepted;

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
