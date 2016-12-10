<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ApplicationAccepted;
use App\Modules\Visittransfer\Jobs\SendTrainingTeamNewAcceptedApplicationEmail;

class NotifyTrainingDepartmentOfAcceptedApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationAccepted $event)
    {
        $confirmationEmailJob = new SendTrainingTeamNewAcceptedApplicationEmail($event->application);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
