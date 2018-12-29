<?php

namespace App\Http\Controllers\UKCP;

use App\Http\Controllers\BaseController;
use App\Libraries\UKCP as UKCPLibrary;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class Token extends BaseController
{
    /** @var Token */
    private $ukcp;

    public function __construct(UKCPLibrary $ukcp)
    {
        $this->ukcp = $ukcp;

        parent::__construct();
    }

    public function create()
    {
        $currentTokens = $this->ukcp->getValidTokensFor(auth()->user());

        if ($currentTokens->count() >= 4) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('You currently have the maximum number of keys created. Please consider deleting unused ones first.');
        }

        $newToken = $this->ukcp->createTokenFor(auth()->user());

        $latestId = $this->ukcp->getValidTokensFor(auth()->user())->first()->id;
        $tokenPath = 'ukcp/tokens/' . auth()->user()->id . '/' . $latestId . '.json';
        Storage::disk('public')->put($tokenPath, $newToken);

        return Redirect::route('mship.manage.dashboard')
            ->withSuccess('Key has been successfully created.');
    }

    public function destroy($tokenId)
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
