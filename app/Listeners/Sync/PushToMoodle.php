<?php

namespace App\Listeners\Sync;

use App\Events\Mship\AccountBanTouch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushToMoodle
{
    public function __construct()
    {
        //
    }

    public function handle(AccountTouched $event)
    {
        // Access the ban using $event->ban
    }
}
