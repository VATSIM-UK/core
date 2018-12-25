<?php

namespace App\Events\VisitTransfer;

use Illuminate\Queue\SerializesModels;
use App\Models\VisitTransfer\Application;

class ApplicationRejected extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
