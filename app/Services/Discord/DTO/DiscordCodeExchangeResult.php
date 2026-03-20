<?php

namespace App\Services\Discord\DTO;

class DiscordCodeExchangeResult
{
    public function __construct(
        public bool $ok,
        public mixed $token = null,
        public mixed $discordUser = null,
        public ?string $message = null
    ) {}

    public static function success(mixed $token, mixed $discordUser): self
    {
        return new self(true, $token, $discordUser);
    }

    public static function failure(string $message): self
    {
        return new self(false, null, null, $message);
    }
}
