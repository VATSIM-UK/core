<?php

namespace App\Listeners\Sync\Bans;

use App\Events\Mship\Bans\BanRepealed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushRepealToForum
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
     * @param  BanRepealed  $event
     * @return void
     */
    public function handle(BanRepealed $event)
    {
        $ban = $event->ban;
        $account = $event->ban->account;

        \Log::info($account->real_name . " was unbanned");
    }
}
