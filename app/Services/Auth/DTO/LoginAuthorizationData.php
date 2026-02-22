<?php

namespace App\Services\Auth\DTO;

class LoginAuthorizationData
{
    public function __construct(
        public string $authorizationUrl,
        public string $state
    ) {}
}
