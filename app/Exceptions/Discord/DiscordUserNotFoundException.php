<?php

namespace App\Exceptions\Discord;

class DiscordUserNotFoundException extends \Exception
{
    public function __construct(string $response)
    {
        $this->message = $response;
    }

    public function __toString()
    {
        return $this->message;
    }
}
