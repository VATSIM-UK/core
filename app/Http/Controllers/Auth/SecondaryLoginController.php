<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\SecondaryLoginService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecondaryLoginController extends BaseController
{
    use AuthenticatesUsers;

    public function __construct(private SecondaryLoginService $secondaryLoginService)
    {
        parent::__construct();
    }

    public function loginSecondary(Request $request)
    {
        $guardResult = $this->secondaryLoginService->validateSecondaryGuard(Auth::guard('vatsim-sso')->check());

        if (! $guardResult->canContinue) {
            return redirect()->route('landing')
                ->withError((string) $guardResult->errorMessage);
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
        return $this->secondaryLoginService->credentialsFromPassword((int) Auth::guard('vatsim-sso')->id(), $request->input('password'));
    }
}
