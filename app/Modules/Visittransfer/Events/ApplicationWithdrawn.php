<?php

namespace App\Modules\Visittransfer\Events;

use Illuminate\Queue\SerializesModels;
use App\Modules\Visittransfer\Models\Application;

class ApplicationWithdrawn extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->load('referees.account')->load('facility');
    }
}
