<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Notifications\ApplicantReferenceRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceRejected;

class NotifyApplicantOfReferenceRejection implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceRejected $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicantReferenceRejected($reference));
    }
}
