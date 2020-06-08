<?php

namespace App\Exceptions\Discord;

use App\Models\Mship\Account;

class InvalidDiscordRemovalException extends \Exception
{
    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->message = 'There was an error removing '.$account->name.' from Discord.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
