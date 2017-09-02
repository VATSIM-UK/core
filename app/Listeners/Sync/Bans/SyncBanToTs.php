<?php

namespace App\Listeners\Sync\Bans;

use Artisan;
use App\Events\Mship\Bans\AccountBanned;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncBanToTs
{
    public function __construct()
    {
        //
    }

    public function handle(\App\Events\Event $event)
    {
        // Run TeamSpeak Manager to ban the user from TS if they are currently connected
        $teaman = Artisan::queue('teaman:runner');
    }
}
