<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceDeleted;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Jobs\SendRefereeNoLongerRequiredEmail;

class NotifyRefereeOfReferenceDeletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceDeleted $event)
    {
        $reference = $event->reference;

        if (!$reference->is_requested) {
            return;
        }

        $reference->notify(new ApplicationReferenceCancelled($reference));
    }
}
