<?php

namespace App\Services\Mship\DTO;

class ManagementRedirectResult
{
    public function __construct(
        public string $route,
        public ?string $level = null,
        public ?string $message = null
    ) {}

    public function hasFlashMessage(): bool
    {
        return $this->level !== null && $this->message !== null;
    }
}
