<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Libraries\UKCP as UKCPLibrary;
use Illuminate\Support\Facades\Redirect;

class UKCP extends BaseController
{
    /** @var UKCP */
    private $ukcp;

    public function __construct(UKCPLibrary $ukcp)
    {
        $this->ukcp = $ukcp;
    }

    public function deleteToken($tokenId)
    {
        $delete = $this->ukcp->deleteToken($tokenId);

        if (!$delete) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('An unknown error occured, please contact Web Services.');
        }

        return Redirect::route('mship.manage.dashboard')
            ->withSuccess('Key has been deleted.');
    }
}
