<?php

namespace App\Services\Mship\DTO;

class NotificationAcknowledgeResult
{
    public function __construct(
        public string $status,
        public ?string $redirectUrl = null
    ) {}

    public static function alreadyRead(): self
    {
        return new self('already_read');
    }

    public static function forcedReturn(string $redirectUrl): self
    {
        return new self('forced_return', $redirectUrl);
    }

    public static function continue(string $redirectUrl): self
    {
        return new self('continue', $redirectUrl);
    }

    public function isAlreadyRead(): bool
    {
        return $this->status === 'already_read';
    }
}
