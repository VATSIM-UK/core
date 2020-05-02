<?php

namespace App\Events\Discord;

use App\Events\Event;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;

class DiscordLinked extends Event
{
    use SerializesModels;

    /* @var Account */
    public $account;

    /* @var int */
    public $discordId;

    public function __construct(Account $account, int $discordId)
    {
        $this->account = $account;
        $this->discordId = $discordId;
    }
}
