<?php

namespace App\Services\External\VatsimNet\Webhooks\DTO;

class ProcessWebhookHttpResult
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $statusCode,
        public array $payload
    ) {}
}
