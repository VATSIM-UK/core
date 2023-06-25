<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;

class LocalBanDisplayController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        if (! Auth::user()->is_system_banned) {
            // don't allow non-banned users to see the contents of the view.
            return redirect()->route('mship.manage.dashboard');
        }

        $localBan = Auth::user()->system_ban->load('reason');

        return $this->viewMake('errors.banned-local')->with(['ban' => $localBan]);
    }
}
