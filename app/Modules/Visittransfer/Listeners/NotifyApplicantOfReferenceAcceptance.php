<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceAccepted;
use App\Modules\Visittransfer\Jobs\SendApplicantReferenceAcceptanceEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceAcceptance implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceAccepted $event)
    {
        $confirmationEmailJob = new SendApplicantReferenceAcceptanceEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
