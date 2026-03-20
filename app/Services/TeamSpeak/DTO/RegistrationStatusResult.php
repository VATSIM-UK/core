<?php

namespace App\Services\TeamSpeak\DTO;

class RegistrationStatusResult
{
    public function __construct(
        public bool $ok,
        public ?string $status = null
    ) {}

    public static function forbidden(): self
    {
        return new self(false);
    }

    public static function success(string $status): self
    {
        return new self(true, $status);
    }
}
