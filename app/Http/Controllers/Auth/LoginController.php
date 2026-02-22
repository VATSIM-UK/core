<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\LoginFlowService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen. The controller uses a trait
 * to conveniently provide its functionality to your applications.
 */
class LoginController extends BaseController
{
    use AuthenticatesUsers;

    public function __construct(private LoginFlowService $loginFlowService)
    {
        parent::__construct();
    }

    public function login(Request $request)
    {
        if (! $request->has('code') || ! $request->has('state')) {
            $authorizationData = $this->loginFlowService->getAuthorizationData();
            $request->session()->put('vatsimauthstate', $authorizationData->state);

            return redirect()->away($authorizationData->authorizationUrl);
        }

        if (! $this->loginFlowService->isValidState((string) $request->input('state'), $request->session()->pull('vatsimauthstate'))) {
            return redirect()->route('landing')->withError('Something went wrong, please try again.');
        }

        return $this->verifyLogin($request);
    }

    protected function verifyLogin(Request $request)
    {
        $result = $this->loginFlowService->authenticateFromCode(
            (string) $request->input('code'),
            (string) $request->ip()
        );

        if (! $result->ok) {
            if ($result->reason === 'missing_permissions') {
                return redirect()->route('landing')->withError('You cannot use our services unless you provide the relevant permissions upon login. Please try again.');
            }

            return redirect()->route('landing')->withError('Something went wrong, please try again.');
        }

        $account = $result->account;

        Auth::guard('vatsim-sso')->loginUsingId($account->id);

        if ($account->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        $intended = $this->loginFlowService->pullIntendedUrl(route('site.home'));

        Auth::login(Auth::guard('vatsim-sso')->user(), true);

        return redirect($intended);
    }
}
