<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Application;

class ApplicationNotUnderReviewException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'This application is not under review and as such cannot be accepted/rejected.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
