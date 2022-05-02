<?php

namespace App\Listeners\NetworkData;

use App\Events\NetworkData\AtcSessionEnded;
use Illuminate\Contracts\Queue\ShouldQueue;

class AtcSessionRecordedSuccessNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(AtcSessionEnded $event)
    {
        //
    }
}
