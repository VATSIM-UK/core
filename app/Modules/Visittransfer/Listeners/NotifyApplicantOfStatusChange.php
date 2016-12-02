<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationStatusChanged;
use App\Modules\Visittransfer\Jobs\SendApplicantStatusChangeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfStatusChange implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationStatusChanged $event)
    {
        $confirmationEmailJob = new SendApplicantStatusChangeEmail($event->application);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
