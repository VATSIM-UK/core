<?php

namespace App\Services\Auth\DTO;

class LoginStateValidationResult
{
    public function __construct(
        public bool $valid,
        public ?string $errorMessage = null
    ) {}

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }
}
