<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Application;

class ApplicationNotReopenableException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'This application is not rejected and as such cannot be reopened for review.';
    }

    public function __toString()
    {
        return $this->message;
    }
}