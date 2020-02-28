<?php

namespace App\Http\Controllers\UKCP;

use App\Http\Controllers\BaseController;
use App\Libraries\UKCP as UKCPLibrary;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Token extends BaseController
{
    /** @var Token */
    private $ukcp;

    public function __construct(UKCPLibrary $ukcp)
    {
        $this->ukcp = $ukcp;

        parent::__construct();
    }

    public function refresh()
    {
        $currentTokens = $this->ukcp->getValidTokensFor(auth()->user());

        foreach ($currentTokens as $token) {
            $this->ukcp->deleteToken($token->id);
        }

        $newToken = $this->ukcp->createTokenFor(auth()->user());

        if (!$newToken) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('An unknown error occured, please contact Web Services.');
        }

        $latestId = $this->ukcp->getValidTokensFor(auth()->user())->first()->id;
        $tokenPath = 'ukcp/tokens/' . auth()->user()->id . '/' . $latestId . '.json';
        Storage::disk('local')->put($tokenPath, $newToken);

        return redirect()->route('ukcp.guide')->withSuccess('Tokens Updated!');
    }

    public function show()
    {
        $latestId = $this->ukcp->getValidTokensFor(auth()->user());

        if ($latestId->isEmpty()) {
            return Redirect::route('ukcp.token.guide');
        }

        return $this->viewMake('ukcp.token.guide')->with('newToken', $latestId->first()->id);
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

    public function download($tokenId)
    {
        $tokenPath = storage_path('app/ukcp/tokens/') . auth()->user()->id . '/' . $tokenId . '.json';
        $headers = array(
            'Content-Type: application/json',
        );

        try {
            return response()->download($tokenPath, substr($tokenId, -8) . '.json', $headers);
        } catch (FileNotFoundException $e) {
            return redirect()->back()->with('error', 'There was an issue downloading your file. Please contact Web Services.');
        }
    }
}
