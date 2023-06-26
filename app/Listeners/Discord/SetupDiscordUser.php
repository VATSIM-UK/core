<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordLinked;
use App\Jobs\Mship\SyncToDiscord;
use App\Libraries\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     * @return void
     */
    public function handle(DiscordLinked $event)
    {
        $event->account->discord_id = $event->discordId;
        $event->account->discord_access_token = $event->discordAccessToken;
        $event->account->discord_refresh_token = $event->discordRefreshToken;
        $event->account->save();

        $this->discord->invite($event->account);

        SyncToDiscord::dispatch($event->account)->onQueue('default');
    }
}
