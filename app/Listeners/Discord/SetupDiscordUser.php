<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordLinked;
use App\Libraries\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class SetupDiscordUser implements ShouldQueue
{
    public $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * Handle the event.
     *
     * @param  DiscordLinked  $event
     * @return void
     */
    public function handle(DiscordLinked $event)
    {
        $event->account->discord_id = $event->discordId;
        $event->account->discord_access_token = $event->discordAccessToken;
        $event->account->discord_refresh_token = $event->discordRefreshToken;
        $event->account->save();

        $this->discord->invite($event->account);

        Artisan::call('discord:manager --force='.$event->account->id);
    }
}
