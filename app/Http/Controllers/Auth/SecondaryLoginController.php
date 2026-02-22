<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\SecondaryLoginService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class SecondaryLoginController extends BaseController
{
    use AuthenticatesUsers;

    public function __construct(private SecondaryLoginService $secondaryLoginService)
    {
        parent::__construct();
    }

    public function loginSecondary(Request $request)
    {
        if (! $this->secondaryLoginService->hasPrimarySsoSession()) {
            return redirect()->route('landing')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        $this->secondaryLoginService->useWebGuard();

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
        return $this->secondaryLoginService->credentialsFromPassword($request->input('password'));
    }
}
