<?php

namespace App\Http\Controllers\UKCP;

use App\Http\Controllers\BaseController;
use App\Libraries\UKCP as UKCPLibrary;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;

class Token extends BaseController
{
    /** @var Token */
    private $ukcp;

    public function __construct(UKCPLibrary $ukcp)
    {
        $this->ukcp = $ukcp;

        parent::__construct();
    }

    public function invalidate()
    {
        $currentTokens = $this->ukcp->getValidTokensFor(auth()->user());

        foreach ($currentTokens as $token) {
            $this->ukcp->deleteToken($token->id, auth()->user());
        }

        return redirect()->route('ukcp.guide')->withSuccess('Tokens Invalidated!');
    }

    public function show()
    {
        return $this->viewMake('ukcp.token.guide');
    }
}
