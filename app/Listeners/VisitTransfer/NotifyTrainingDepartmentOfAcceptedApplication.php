<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ApplicationAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTrainingDepartmentOfAcceptedApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationAccepted $event)
    {
        $application = $event->application;
        $application->facility->notify(new \App\Notifications\ApplicationAccepted($application));
    }
}
