<?php

namespace App\Events\VisitTransfer;

use App\Models\VisitTransfer\Application;
use Illuminate\Queue\SerializesModels;

class ApplicationCompleted extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
