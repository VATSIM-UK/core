<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Application;

class ApplicationCannotBeEditedException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'Application #'.$this->application->id.' is no longer in progress and cannot be edited.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
