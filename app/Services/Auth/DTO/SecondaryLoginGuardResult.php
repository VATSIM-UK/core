<?php

namespace App\Services\Auth\DTO;

class SecondaryLoginGuardResult
{
    public function __construct(
        public bool $canContinue,
        public ?string $errorMessage = null
    ) {}

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }
}
