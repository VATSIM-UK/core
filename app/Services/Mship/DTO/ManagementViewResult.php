<?php

namespace App\Services\Mship\DTO;

class ManagementViewResult
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public bool $redirect,
        public string $route,
        public array $data = [],
        public ?string $level = null,
        public ?string $message = null
    ) {}

    public function hasFlashMessage(): bool
    {
        return $this->level !== null && $this->message !== null;
    }
}
