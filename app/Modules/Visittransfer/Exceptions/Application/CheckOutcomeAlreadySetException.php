<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Application;

class CheckOutcomeAlreadySetException extends \Exception
{
    private $application;
    private $check;

    public function __construct(Application $application, $check)
    {
        $this->application = $application;
        $this->check = $check;

        $this->message = "The check '".$check."' already has an outcome.";
    }

    public function __toString()
    {
        return $this->message;
    }
}
