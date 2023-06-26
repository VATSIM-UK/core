<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
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
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge(['id' => Auth::guard('vatsim-sso')->user()->id], $request->only(
            'password', 'password_confirmation', 'token'
        ));
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
        $user->setPassword($password);

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }
}
