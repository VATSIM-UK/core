<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

class SecondaryLoginService
{
    public function hasPrimarySsoSession(): bool
    {
        return Auth::guard('vatsim-sso')->check();
    }

    public function useWebGuard(): void
    {
        Auth::shouldUse('web');
    }

    /**
     * @return array{id: int, password: mixed}
     */
    public function credentialsFromPassword(mixed $password): array
    {
        return [
            'id' => Auth::guard('vatsim-sso')->user()->id,
            'password' => $password,
        ];
    }
}
