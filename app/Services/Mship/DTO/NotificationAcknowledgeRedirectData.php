<?php

namespace App\Services\Mship\DTO;

class NotificationAcknowledgeRedirectData
{
    public function __construct(
        public string $route,
        public ?string $redirectUrl = null
    ) {}

    public function usesRouteRedirect(): bool
    {
        return $this->redirectUrl === null;
    }
}
