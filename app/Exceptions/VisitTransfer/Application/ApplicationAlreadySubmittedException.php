<?php

namespace App\Exceptions\VisitTransferLegacy\Application;

use App\Models\VisitTransferLegacy\Application;

class ApplicationAlreadySubmittedException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'Application #'.$this->application->id.' has already been submitted.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
