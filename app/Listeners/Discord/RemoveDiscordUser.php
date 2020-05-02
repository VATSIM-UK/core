<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\InvalidDiscordRemovalException;
use App\Libraries\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveDiscordUser implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param DiscordUnlinked $event
     * @return void
     */
    public function handle(DiscordUnlinked $event)
    {
        $account = $event->account;
        $discord = new Discord();

        $role = $discord->removeRole($account, 'Member');
        $kick = $discord->kick($account);

        if (!$role && !$kick) {
            throw new InvalidDiscordRemovalException($account);
        }

        $account->discord_id = null;
        $account->save();
    }
}
