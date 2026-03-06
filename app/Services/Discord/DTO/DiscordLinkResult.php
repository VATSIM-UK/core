<?php

namespace App\Services\Discord\DTO;

class DiscordLinkResult
{
    public function __construct(
        public bool $ok,
        public ?string $message = null
    ) {}

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
