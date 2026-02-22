<?php

namespace App\Services\Auth\DTO;

use App\Models\Mship\Account;

class LoginAttemptResult
{
    public function __construct(
        public bool $ok,
        public ?string $reason = null,
        public ?Account $account = null
    ) {}

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }

    public static function success(Account $account): self
    {
        return new self(true, null, $account);
    }
}
