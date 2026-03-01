<?php

namespace App\Services\Mship\DTO;

class RetentionTokenResult
{
    /**
     * @param  array<string, string>  $flash
     */
    public function __construct(
        public string $route,
        public array $flash = []
    ) {}
}
