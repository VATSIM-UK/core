<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorSetupController extends BaseController
{
    public function show(Request $request)
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $this->setTitle('Two-Factor Authentication');

            return $this->viewMake('auth.two-factor.manage')
                ->with('recoveryCodes', $user->recoveryCodes());
        }

        $this->setTitle('Two-Factor Authentication Setup');

        return $this->viewMake('auth.two-factor.setup')
            ->with('pendingConfirmation', ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at));
    }
}
