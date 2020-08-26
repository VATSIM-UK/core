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

    /* @var string */
    public $discordAccessToken;

    /* @var string */
    public $discordRefreshToken;

    public function __construct(Account $account, $discordUser, $token)
    {
        $this->account = $account;
        $this->discordId = $discordUser->getId();
        $this->discordAccessToken = $token->getToken();
        $this->discordRefreshToken = $token->getRefreshToken();
    }
}
