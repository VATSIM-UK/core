<?php

namespace App\Http\Middleware;

use App\Traits\Middleware\RedirectsOnFailure;
use Auth;

class MandatoryPasswords
{
    use RedirectsOnFailure;

    protected $except = [
        'password/create',
        'password/change',
    ];

    public function validate($makeResponse)
    {
        if (Auth::check()) {
            if (Auth::user()->mandatory_password && !Auth::user()->hasPassword()) {
                if ($makeResponse) {
                    return redirect()->guest(route('password.create'))->withError('You are required to set a secondary password.');
                } else {
                    return true;
                }
            } elseif (Auth::user()->hasPasswordExpired()) {
                if ($makeResponse) {
                    return redirect()->guest(route('password.change'))->withError('Your password has expired.');
                } else {
                    return true;
                }
            }
        }
    }
}
