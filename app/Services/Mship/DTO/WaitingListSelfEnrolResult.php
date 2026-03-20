<?php

namespace App\Services\Mship\DTO;

class WaitingListSelfEnrolResult
{
    public function __construct(
        public bool $allowed,
        public ?string $message = null
    ) {}

    public static function denied(string $message): self
    {
        return new self(false, $message);
    }

    public static function allowed(): self
    {
        return new self(true);
    }
}
