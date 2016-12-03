<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Jobs\SendRefereeNoLongerRequiredEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRefereeOfReferenceDeletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        if (! $event->reference->is_requested) {
            return;
        }

        $confirmationEmailJob = new SendRefereeNoLongerRequiredEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
