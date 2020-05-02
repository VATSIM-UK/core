<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordLinked;
use Illuminate\Contracts\Queue\ShouldQueue;

class GrantDiscordRole implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param DiscordLinked $event
     * @return void
     */
    public function handle(DiscordLinked $event)
    {
        $account = $event->account;

        // Get Discord User
        $account->discord_id;

        // Find the Member role
        //

        // Give the user the member role
        //
    }
}
