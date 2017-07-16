<?php

namespace App\Events\VisitTransfer;

use App\Events\Event;
use App\Models\VisitTransfer\Application;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Event
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->load('referees.account')->load('facility');
    }
}
