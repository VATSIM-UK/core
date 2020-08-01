<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SecondaryLoginController extends BaseController
{
    use AuthenticatesUsers;

    public static function attemptSecondaryAuth(Account $account)
    {
        if ($account->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        $intended = Session::pull('url.intended', route('site.home'));

        Auth::login(Auth::guard('vatsim-sso')->user(), true);

        return redirect($intended);
    }

    public function loginSecondary(Request $request)
    {
        if (! Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('dashboard')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        Auth::shouldUse('web');

        return $this->login($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return ['id' => Auth::guard('vatsim-sso')->user()->id, 'password' => $request->input('password')];
    }
}
