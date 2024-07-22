<?php

namespace App\Exceptions\VisitTransferLegacy\Application;

use App\Models\VisitTransferLegacy\Application;

class ApplicationNotRejectableException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'This application is not submitted or under review and as such cannot be rejected.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
