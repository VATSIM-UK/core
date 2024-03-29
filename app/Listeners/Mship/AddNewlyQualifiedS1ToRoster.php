<?php

namespace App\Listeners\Mship;

use App\Events\Mship\Qualifications\QualificationAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddNewlyQualifiedS1ToRoster
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QualificationAdded $event): void
    {
        //
    }
}
