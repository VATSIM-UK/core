<?php

namespace App\Http\Middleware;

use App\Traits\Middleware\ExcludesRoutes;
use App\Traits\Middleware\RedirectsOnFailure;
use Auth;
use Closure;
use Request;
use Session;
use Redirect;
use Symfony\Component\VarDumper\VarDumper;

class UserMustReadNotifications
{
    use RedirectsOnFailure;

    protected $except = [
        'mship/notification/list',
        'mship/notification/acknowledge/*',
    ];

    public function validate($makeResponse)
    {
        if (Auth::check() &&
            (Auth::user()->has_unread_important_notifications || Auth::user()->has_unread_must_acknowledge_notifications)
        ) {
            if ($makeResponse) {
                return redirect()->guest(route('mship.notification.list'));
            } else {
                return true;
            }
        }
    }
}
