<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceAccepted;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceAccepted;

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
