<?php

namespace App\Services\VisitTransfer\DTO;

class ApplicationContinueRedirectData
{
    /**
     * @param  array<int, string>  $routeParameters
     */
    public function __construct(
        public string $route,
        public array $routeParameters = []
    ) {}
}
