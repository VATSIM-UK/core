<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SecondaryLoginController extends BaseController
{
    public static function attemptSecondaryAuth()
    {
        $member = Auth::guard('vatsim-sso')->user();

        if ($member->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        $intended = Session::pull('url.intended', route('site.home'));

        Auth::login(Auth::guard('vatsim-sso')->user(), true);

        return redirect($intended);
    }

    public function loginSecondary(Request $request)
    {
        if (!Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('dashboard')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        Auth::shouldUse('web');
        $response = $this->login($request);

        return $response;
    }
}
