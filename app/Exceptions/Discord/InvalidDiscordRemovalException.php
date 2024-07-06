<?php

namespace App\Exceptions\Discord;

use App\Models\Mship\Account;

class InvalidDiscordRemovalException extends GenericDiscordException
{
    public function __construct(private Account $account)
    {
        $this->message = 'There was an error removing '.$account->name.' from Discord.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
