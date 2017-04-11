<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Notifications\ApplicantReferenceSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;

class NotifyApplicantOfReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $reference = $event->reference;
        $reference->application->account->notify(new ApplicantReferenceSubmitted($reference));
    }
}
