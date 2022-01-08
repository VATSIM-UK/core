<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Application;

class ApplicationNotAcceptedException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'This application has not been accepted so cannot be completed or cancelled.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
