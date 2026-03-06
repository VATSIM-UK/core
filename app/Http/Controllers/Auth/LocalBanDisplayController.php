<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\LocalBanDisplayService;
use Illuminate\Support\Facades\Auth;

class LocalBanDisplayController extends BaseController
{
    public function __construct(private LocalBanDisplayService $localBanDisplayService)
    {
        parent::__construct();
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $account = Auth::user();

        $localBan = $this->localBanDisplayService->getBanForDisplay($account);

        if ($localBan === null) {
            // don't allow non-banned users to see the contents of the view.
            return redirect()->route('mship.manage.dashboard');
        }

        return $this->viewMake('errors.banned-local')->with(['ban' => $localBan]);
    }
}
