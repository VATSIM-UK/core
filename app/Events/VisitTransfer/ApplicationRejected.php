<?php

namespace App\Events\VisitTransfer;

use App\Models\VisitTransfer\Application;

class ApplicationRejected extends ApplicationStatusChanged
{
    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
