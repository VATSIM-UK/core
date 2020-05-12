<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Application;

class TooManyRefereesException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'You cannot add more than '.$this->application->references_required.' referees.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
