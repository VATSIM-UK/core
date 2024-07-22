<?php

namespace App\Exceptions\VisitTransferLegacy\Application;

use App\Models\VisitTransferLegacy\Application;

class ApplicationCannotBeExpiredException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'Application #'.$this->application->id.' cannot be expired.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
