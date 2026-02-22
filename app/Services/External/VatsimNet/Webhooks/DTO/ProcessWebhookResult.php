<?php

namespace App\Services\External\VatsimNet\Webhooks\DTO;

class ProcessWebhookResult
{
    public function __construct(
        public string $status,
        public ?string $message = null
    ) {}

    public static function ok(): self
    {
        return new self('ok');
    }

    public static function unknownAction(string $action): self
    {
        return new self('unknown_action', $action);
    }

    public function isOk(): bool
    {
        return $this->status === 'ok';
    }
}
