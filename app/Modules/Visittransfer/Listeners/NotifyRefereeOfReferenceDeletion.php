<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ReferenceDeleted;
use App\Modules\Visittransfer\Jobs\SendRefereeConfirmationEmail;

class NotifyRefereeOnReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceDeleted $event)
    {
        $confirmationEmailJob = new SendRefereeConfirmationEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
