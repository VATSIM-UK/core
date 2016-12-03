<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Jobs\SendApplicantReferenceSubmissionEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $confirmationEmailJob = new SendApplicantReferenceSubmissionEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
