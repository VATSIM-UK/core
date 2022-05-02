<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;

class LogoutController extends BaseController
{
    public function __invoke()
    {
        Auth::logout();

        return redirect()->route('site.home');
    }
}
