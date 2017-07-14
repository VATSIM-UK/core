<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ApplicationStatusChanged;

class NotifyApplicantOfStatusChange implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationStatusChanged $event)
    {
        $application = $event->application;
        $application->account->notify(new \App\Notifications\ApplicationStatusChanged($application));
    }
}
