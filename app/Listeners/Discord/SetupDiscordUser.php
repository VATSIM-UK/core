<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordLinked;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetupDiscordUser implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  DiscordLinked  $event
     * @return void
     */
    public function handle(DiscordLinked $event)
    {
        $event->account->discord_id = $event->discordId;
        $event->account->save();
    }
}
