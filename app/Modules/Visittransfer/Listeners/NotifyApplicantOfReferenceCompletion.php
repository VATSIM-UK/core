<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Jobs\SendApplicantReferenceSubmissionEmail;

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
