<?php

namespace App\Listeners\Sync;

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
