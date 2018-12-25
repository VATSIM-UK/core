<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ApplicationStatusChanged as ApplicationStatusChangedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfStatusChange implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationStatusChangedEvent $event)
    {
        $application = $event->application;
        $application->account->notify(new \App\Notifications\ApplicationStatusChanged($application));
    }
}
