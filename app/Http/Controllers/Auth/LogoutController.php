<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends BaseController
{
    public function __invoke(Request $request)
    {
        $request->session()->forget([
            'login.id',
            'login.remember',
            'auth.password_confirmed_at',
        ]);

        Auth::guard('web')->logout();
        Auth::guard('vatsim-sso')->logout();

        return redirect()->route('site.home');
    }
}
