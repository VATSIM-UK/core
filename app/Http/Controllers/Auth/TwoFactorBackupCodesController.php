<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorBackupCodesController extends BaseController
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.setup');
        }

        $this->setTitle('Save Your Recovery Codes');

        return $this->viewMake('auth.two-factor.backup-codes')
            ->with('recoveryCodes', $user->recoveryCodes());
    }
}
