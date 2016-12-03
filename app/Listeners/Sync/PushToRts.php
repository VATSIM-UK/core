<?php

namespace App\Listeners\Sync;

class PushToRts
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
