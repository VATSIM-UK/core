<?php

namespace App\Modules\Visittransfer\Events;

use App\Modules\Visittransfer\Models\Application;
use Illuminate\Queue\SerializesModels;

class ApplicationUnderReview extends ApplicationStatusChanged
{
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
