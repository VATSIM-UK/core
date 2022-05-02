<?php

namespace App\Events\Discord;

use App\Events\Event;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;

class DiscordUnlinked extends Event
{
    use SerializesModels;

    /* @var Account */
    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
