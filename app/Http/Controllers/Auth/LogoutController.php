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
            'auth_override',
        ]);

        Auth::guard('web')->logout();
        Auth::guard('vatsim-sso')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }
}
