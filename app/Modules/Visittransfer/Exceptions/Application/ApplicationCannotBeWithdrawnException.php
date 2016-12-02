<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Application;

class ApplicationCannotBeWithdrawnException extends \Exception
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->message = 'Application #'.$this->application->id.' cannot be withdrawn.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
