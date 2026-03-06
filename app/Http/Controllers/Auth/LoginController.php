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
        if ($this->loginFlowService->shouldStartAuthorizationFlow($request->has('code'), $request->has('state'))) {
            $authorizationData = $this->loginFlowService->getAuthorizationData();
            $request->session()->put('vatsimauthstate', $authorizationData->state);

            return redirect()->away($authorizationData->authorizationUrl);
        }

        $stateValidation = $this->loginFlowService->validateState((string) $request->input('state'), $request->session()->pull('vatsimauthstate'));

        if (! $stateValidation->valid) {
            return redirect()->route('landing')->withError((string) $stateValidation->errorMessage);
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
            return redirect()->route('landing')->withError($this->loginFlowService->loginFailureMessage((string) $result->reason));
        }

        $account = $result->account;

        Auth::guard('vatsim-sso')->loginUsingId($account->id);

        if ($this->loginFlowService->requiresSecondaryLogin($account)) {
            return redirect()->route('auth-secondary');
        }

        $intended = (string) $request->session()->pull('url.intended', route('site.home'));

        Auth::login(Auth::guard('vatsim-sso')->user(), true);

        return redirect($intended);
    }
}
