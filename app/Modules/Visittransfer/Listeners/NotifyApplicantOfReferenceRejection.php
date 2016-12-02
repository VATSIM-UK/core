<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceRejected;
use App\Modules\Visittransfer\Jobs\SendApplicantReferenceRejectionEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceRejection implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceRejected $event)
    {
        $confirmationEmailJob = new SendApplicantReferenceRejectionEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
