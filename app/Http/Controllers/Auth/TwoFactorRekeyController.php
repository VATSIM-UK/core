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
        $user = $request->user();

        $enable($user, true);

        // Fortify's enable action rotates the secret and recovery codes but leaves
        // two_factor_confirmed_at alone, which would leave the new, never-scanned
        // secret marked as confirmed - locking the member out of an account whose
        // old authenticator has just stopped working. Clear it so the setup page
        // shows them the new QR code to confirm.
        $user->forceFill(['two_factor_confirmed_at' => null])->save();

        return redirect()->route('two-factor.setup')
            ->withSuccess('Scan the new QR code with your authenticator application, then enter a code to finish.');
    }
}
