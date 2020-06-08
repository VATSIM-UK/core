<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordLinked;
use App\Exceptions\Discord\InvalidDiscordSetupException;
use App\Libraries\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetupDiscordUser implements ShouldQueue
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
        $discordId = $event->discordId;
        $discord = app()->make(Discord::class);

        $account->discord_id = $discordId;

        $role = $discord->grantRole($event->account, 'Member');
        $nickname = $discord->setNickname($account, $account->name);

        if (! $role || ! $nickname) {
            throw new InvalidDiscordSetupException($account);
        }

        $account->save();
    }
}
