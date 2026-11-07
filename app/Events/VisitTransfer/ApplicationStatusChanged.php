<?php

namespace App\Events\VisitTransfer;

use App\Events\Event;
use App\Models\VisitTransfer\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Event
{
    use SerializesModels {
        restoreModel as restoreModelFromTrait;
    }

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->load('facility');
    }

    public function restoreModel($value)
    {
        try {
            return $this->restoreModelFromTrait($value);
        } catch (ModelNotFoundException $exception) {
            if ($value->class !== Application::class || $exception->getModel() !== Application::class) {
                throw $exception;
            }

            return null;
        }
    }
}
