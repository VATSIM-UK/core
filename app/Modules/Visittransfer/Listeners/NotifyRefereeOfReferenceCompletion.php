<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceSubmitted;

class NotifyRefereeOfReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $reference = $event->reference;

        if (!$reference->is_requested) {
            return;
        }

        $reference->notify(new ApplicationReferenceSubmitted($reference));
    }
}
