<?php

namespace App\Modules\Vt\Events;

use App\Events\Event;

use App\Modules\Vt\Models\Application;
use Illuminate\Queue\SerializesModels;

class ApplicationUpdated extends Event {
    use SerializesModels;

    public $application = null;

    public function __construct(Application $application){
        $this->application = $application;
    }
}