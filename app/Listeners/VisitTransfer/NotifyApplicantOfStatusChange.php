<?php

namespace App\Listeners\VisitTransfer;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ApplicationStatusChanged as ApplicationStatusChangedEvent;
use App\Notifications\ApplicationStatusChanged;

class NotifyApplicantOfStatusChange implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationStatusChangedEvent $event)
    {
        $application = $event->application;
        $application->account->notify(new ApplicationStatusChanged($application));
    }
}
