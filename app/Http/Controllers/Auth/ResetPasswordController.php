<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\PasswordManagementService;
use Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

/**
 * This controller is responsible for handling password reset requests
 * and uses a simple trait to include this behavior. You're free to
 * explore this trait and override any methods you wish to tweak.
 */
class ResetPasswordController extends BaseController
{
    use ResetsPasswords;

    public function __construct(private PasswordManagementService $passwordManagementService)
    {
        parent::__construct();
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'new_password' => 'required|string|confirmed|min:8|upperchars:1|lowerchars:1|numbers:1',
            'new_password_confirmation' => 'required|same:new_password',
        ];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        $credentials = $request->only('new_password', 'new_password_confirmation', 'token');

        return array_merge(['id' => Auth::guard('vatsim-sso')->user()->id], $credentials, ['password' => $credentials['new_password'], 'password_confirmation' => $credentials['new_password_confirmation']]);
    }

    /**
     * Override password reset logic. Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->passwordManagementService->setPassword($user, $password);

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }
}
