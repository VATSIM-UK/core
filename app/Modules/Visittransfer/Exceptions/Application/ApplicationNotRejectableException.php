<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Application;

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
