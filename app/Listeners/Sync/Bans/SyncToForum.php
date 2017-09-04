<?php

namespace App\Listeners\Sync\Bans;

use App\Events\Mship\Bans\AccountBanned;

class SyncToForum
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
    public function handle(\App\Events\Event $event)
    {
        $IPSInitFile = '/var/www/community/init.php';

        if (!file_exists($IPSInitFile)) {
            return;
        }

        require_once $IPSInitFile;
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        if ($account->is_banned) {
            $query = \IPS\Db::i()->update(['core_members', 'm'], ['m.temp_ban', -1], "m.vatsim_cid='".$account->id."'");
        } else {
            $query = \IPS\Db::i()->update(['core_members', 'm'], ['m.temp_ban', 0], "m.vatsim_cid='".$account->id."'");
        }
    }
}
