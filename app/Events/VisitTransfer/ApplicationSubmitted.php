<?php

namespace App\Events\VisitTransferLegacy;

use App\Models\VisitTransferLegacy\Application;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->load('referees.account')->load('facility');
    }
}
