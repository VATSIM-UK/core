<?php

namespace App\Listeners\Sync\Bans;

use App\Events\Mship\Bans\AccountBanned;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushBanToForum
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccountBanned  $event
     * @return void
     */
    public function handle(AccountBanned $event)
    {
        $ban = $event->ban;
        $account = $event->ban->account;

        \Log::info($account->real_name . " was banned");
    }
}
