<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordFlowService
{
    public function sendResetLink(PasswordBroker $broker): string
    {
        return $broker->sendResetLink([
            'id' => Auth::guard('vatsim-sso')->user()->id,
        ]);
    }
}
