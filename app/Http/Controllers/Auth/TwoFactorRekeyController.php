<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class TwoFactorRekeyController extends BaseController
{
    /**
     * Discard the current authenticator secret and issue a fresh one, leaving the
     * account in Fortify's pending-confirmation state so the member must verify
     * their new device before two-factor is considered enabled again.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        $enable($request->user(), true);

        return redirect()->route('two-factor.setup')
            ->withSuccess('Scan the new QR code with your authenticator application, then enter a code to finish.');
    }
}
