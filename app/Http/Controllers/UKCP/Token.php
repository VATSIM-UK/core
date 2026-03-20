<?php

namespace App\Http\Controllers\UKCP;

use App\Http\Controllers\BaseController;
use App\Services\UKCP\TokenService;

class Token extends BaseController
{
    public function __construct(private TokenService $tokenService)
    {
        parent::__construct();
    }

    public function invalidate()
    {
        $this->tokenService->invalidateAll(auth()->user());

        return redirect()->route('ukcp.guide')->withSuccess('Tokens Invalidated!');
    }

    public function show()
    {
        return $this->viewMake('ukcp.token.guide');
    }
}
