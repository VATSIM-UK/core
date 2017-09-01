<?php

namespace App\Listeners\Sync\Bans;

use Artisan;
use App\Events\Mship\Bans\AccountBanned;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncBan
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

        // TeamSpeak

        // Run TeamSpeak Manager to ban the user from TS if they are currently connected
        $teaman = Artisan::queue('teaman:runner');


        // IPB

        $IPSInitFile = '/var/www/community/init.php';

        if(!file_exists($IPSInitFile)){
          return;
        }

        require_once $IPSInitFile;
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $query = \IPS\Db::i()->update(['core_members', 'm'], ['m.temp_ban', -1], "m.vatsim_cid='".$account->id."'");
    }
}
