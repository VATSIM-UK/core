<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Request;
use Session;
use Redirect;

class UserMustReadNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()
            && Session::has('auth_extra')
            && (Auth::user()->has_unread_important_notifications
                || Auth::user()->has_unread_must_acknowledge_notifications
            )
        ) {
            Session::put('force_notification_read_return_url', Request::fullUrl());

            return Redirect::route('mship.notification.list');
        }

        return $next($request);
    }
}
