<?php

namespace App\Exceptions\VisitTransferLegacy\Application;

use App\Models\VisitTransferLegacy\Application;

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
