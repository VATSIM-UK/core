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
     * @return void
     */
    public function handle(DiscordUnlinked $event)
    {
        $account = $event->account;
        $discord = app()->make(Discord::class);

        $kick = $discord->kick($account);

        if (! $kick) {
            throw new InvalidDiscordRemovalException($account);
        }

        $account->discord_id = null;
        $account->save();
    }
}
