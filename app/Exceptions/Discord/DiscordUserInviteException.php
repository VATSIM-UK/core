<?php

namespace App\Exceptions\Discord;

class DiscordUserInviteException extends GenericDiscordException
{
    public function __construct(public string $response, string $message = 'There was an error inviting you to our Discord server.')
    {
        $this->message = $message;
    }
}
