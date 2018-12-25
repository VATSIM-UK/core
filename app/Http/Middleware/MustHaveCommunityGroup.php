<?php

namespace App\Http\Middleware;

use Auth;
use App\Traits\Middleware\RedirectsOnFailure;

class MustHaveCommunityGroup
{
    use RedirectsOnFailure;

    protected $except = [
        'community/membership/deploy',
    ];

    public function validate($makeResponse)
    {
        if (Auth::user()->hasState('DIVISION') && Auth::user()->communityGroups()->count() == 0) {
            if ($makeResponse) {
                return redirect()->guest(route('community.membership.deploy'));
            } else {
                return true;
            }
        }
    }
}
