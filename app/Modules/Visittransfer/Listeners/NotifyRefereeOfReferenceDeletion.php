<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRequest;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceDeleted;
use App\Modules\Visittransfer\Jobs\SendRefereeConfirmationEmail;

class NotifyRefereeOnReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceUnderReview $event)
    {
        $event->reference->notify(new ApplicationReferenceSubmitted($event->reference));
    }
}
