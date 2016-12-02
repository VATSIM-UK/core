<?php

namespace App\Listeners\Sync;

class PushToTeamSpeak
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
