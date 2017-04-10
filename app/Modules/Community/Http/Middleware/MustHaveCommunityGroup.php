<?php

namespace App\Modules\Community\Http\Middleware;

use Auth;
use Closure;
use Request;
use Session;
use Redirect;
use Response;

class MustHaveCommunityGroup
{
    private $excludedRoutes = [
        'community.membership.deploy',
        'community.membership.deploy.post',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array(\Route::current()->getName(), $this->excludedRoutes)) {
            return $next($request);
        }

        if (Request::ajax()) {
            return Response::make('Unauthorised', 401);
        }

        if (!Auth::user()->hasState('DIVISION')) {
            return $next($request);
        }

        if (Auth::user()->communityGroups()->count() > 0) {
            return $next($request);
        }

        Session::put('community_group_return', Request::fullUrl());

        return Redirect::route('community.membership.deploy');
    }
}
