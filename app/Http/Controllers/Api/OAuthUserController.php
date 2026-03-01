<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\OAuthUserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class OAuthUserController
{
    public function __construct(private OAuthUserProfileService $oAuthUserProfileService) {}

    public function view(Request $request)
    {
        $clientId = $request->user()->oAuthToken()->client->id;
        $account = $request->user();

        $data = $this->oAuthUserProfileService->buildResponseData(
            $account,
            (int) $clientId,
            (bool) Session::get('auth_override', false)
        );

        return Response::json(['status' => 'success', 'data' => $data]);
    }
}
