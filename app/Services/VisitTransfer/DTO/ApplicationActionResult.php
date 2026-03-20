<?php

namespace App\Services\VisitTransfer\DTO;

class ApplicationActionResult
{
    /**
     * @param  array<int, mixed>  $routeParameters
     */
    public function __construct(
        public bool $useBackRedirect,
        public string $route,
        public array $routeParameters = [],
        public string $level = 'success',
        public ?string $message = null,
        public bool $withInput = false
    ) {}
}
