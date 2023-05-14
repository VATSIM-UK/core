<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecondaryLoginController extends BaseController
{
    use AuthenticatesUsers;

    public function loginSecondary(Request $request)
    {
        if (! Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('landing')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        Auth::shouldUse('web');

        return $this->login($request);
    }

    /**
     * Validate the user login request.
     *
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
     * @return array
     */
    protected function credentials(Request $request)
    {
        return ['id' => Auth::guard('vatsim-sso')->user()->id, 'password' => $request->input('password')];
    }
}
