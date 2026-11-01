<?php

namespace App\Events\VisitTransfer;

use App\Models\VisitTransfer\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->load('facility');
    }

    public function restoreModel($value)
    {
        try {
            return parent::restoreModel($value);
        } catch (ModelNotFoundException $exception) {
            if ($value->class !== Application::class || $exception->getModel() !== Application::class) {
                throw $exception;
            }

            return null;
        }
    }
}
