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

        require_once '/var/www/community/init.php';
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        // Check if they still have outstanding bans
        if($account->is_banned){
          return;
        }

        // Update user's IPB record
        $query = \IPS\Db::i()->update(['core_members', 'm'], ['m.temp_ban', 0], "m.vatsim_cid='".$account->id."'");
    }
}
