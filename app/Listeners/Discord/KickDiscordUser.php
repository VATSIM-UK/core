<?php

namespace App\Listeners\Discord;

use App\Events\Discord\DiscordUnlinked;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveDiscordUser implements ShouldQueue
{
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'sync';

    /**
     * Handle the event.
     *
     * @param DiscordUnlinked $event
     * @return void
     */
    public function handle(DiscordUnlinked $event)
    {
        $account = $event->account;

        // Get Discord User
        $account->discord_id;

        // Kick the Discord user
        //

        // Update their Discord id
        $account->discord_id = null;
        $account->save();
    }
}
