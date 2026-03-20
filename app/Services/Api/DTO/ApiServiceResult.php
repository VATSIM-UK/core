<?php

namespace App\Services\Api\DTO;

class ApiServiceResult
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $headers
     */
    public function __construct(
        public int $statusCode,
        public array $payload,
        public array $headers = []
    ) {}
}
