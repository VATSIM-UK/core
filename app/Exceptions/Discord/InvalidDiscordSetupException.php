<?php

namespace App\Exceptions\Discord;

use App\Models\Mship\Account;

class InvalidDiscordSetupException extends \Exception
{
    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->message = 'There was an error setting up '.$account->name.' on Discord.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
