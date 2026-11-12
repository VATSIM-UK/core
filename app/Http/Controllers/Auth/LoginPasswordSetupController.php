<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Auth\LoginFlow;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginPasswordSetupController extends BaseController
{
    public function show(Request $request)
    {
        if (! Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('login')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        $account = Auth::guard('vatsim-sso')->user();

        if (! $account->requiresMandatoryPasswordSetup()) {
            return $this->redirectAwayFromSetup($request, $account);
        }

        $this->authorize('setupDuringLogin', 'password');

        return $this->viewMake('auth.passwords.create')->with([
            'heading' => 'Secondary Password Required',
            'intro' => 'Your role requires a secondary password. Create one below to continue signing in.',
            'formAction' => route('login.password.setup.store'),
        ]);
    }

    public function store(Request $request)
    {
        if (! Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('login')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        $account = Auth::guard('vatsim-sso')->user();

        $this->authorize('setupDuringLogin', 'password');

        $this->validate($request, [
            'new_password' => 'required|string|confirmed|min:8|upperchars:1|lowerchars:1|numbers:1',
        ]);

        $account->setPassword($request->input('new_password'));

        return LoginFlow::redirectAfterMandatoryPasswordSetup($request, $account->fresh());
    }

    protected function redirectAwayFromSetup(Request $request, $account)
    {
        if ($account->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        return LoginFlow::establishWebSession($request, $account, true);
    }
}
