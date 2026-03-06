<?php

namespace App\Services\Auth;

use App\Services\Auth\DTO\SecondaryLoginGuardResult;

class SecondaryLoginService
{
    public function validateSecondaryGuard(bool $hasVatsimSso): SecondaryLoginGuardResult
    {
        if (! $hasVatsimSso) {
            return SecondaryLoginGuardResult::deny('Could not authenticate: VATSIM.net authentication is not present.');
        }

        return SecondaryLoginGuardResult::allow();
    }

    /**
     * @return array{id: int, password: mixed}
     */
    public function credentialsFromPassword(int $vatsimId, mixed $password): array
    {
        return [
            'id' => $vatsimId,
            'password' => $password,
        ];
    }
}
