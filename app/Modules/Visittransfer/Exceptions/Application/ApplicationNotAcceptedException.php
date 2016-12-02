<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Application;

class ApplicationNotAcceptedException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'This application is not been accepted so cannot be completed or cancelled.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
