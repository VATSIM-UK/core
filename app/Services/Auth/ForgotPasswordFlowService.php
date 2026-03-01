<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\PasswordBroker;

class ForgotPasswordFlowService
{
    public function sendResetLink(PasswordBroker $broker, int $vatsimId): string
    {
        return $broker->sendResetLink([
            'id' => $vatsimId,
        ]);
    }
}
