<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Application;

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
