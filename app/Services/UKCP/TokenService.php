<?php

namespace App\Services\UKCP;

use App\Libraries\UKCP as UKCPLibrary;
use App\Models\Mship\Account;

class TokenService
{
    public function __construct(private UKCPLibrary $ukcp) {}

    public function invalidateAll(Account $account): void
    {
        $currentTokens = $this->ukcp->getValidTokensFor($account);

        foreach ($currentTokens as $token) {
            $this->ukcp->deleteToken($token->id, $account);
        }
    }
}
