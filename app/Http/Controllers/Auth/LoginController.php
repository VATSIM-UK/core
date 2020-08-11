<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen. The controller uses a trait
 * to conveniently provide its functionality to your applications.
 */
class LoginController extends BaseController
{
    use AuthenticatesUsers;

    protected $provider;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->provider = new VatsimOAuthController();
    }

    public function login(Request $request)
    {
        if (! $request->has('code') || ! $request->has('state')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $request->session()->put('vatsimauthstate', $this->provider->getState());

            return redirect()->away($authorizationUrl);
        }

        if ($request->input('state') !== session()->pull('vatsimauthstate')) {
            return redirect()->route('dashboard')->withError('Something went wrong, please try again.');
        }

        return $this->verifyLogin($request);
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code'),
            ]);
        } catch (IdentityProviderException $e) {
            return redirect()->route('dashboard')->withError('Something went wrong, please try again.');
        }

        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        if (
            ! $resourceOwner->data ||
            ! $resourceOwner->data->cid ||
            ! $resourceOwner->data->personal ||
            ! $resourceOwner->data->personal->name_first ||
            ! $resourceOwner->data->personal->name_last ||
            ! $resourceOwner->data->personal->email ||
            ! $resourceOwner->data->vatsim ||
            ! $resourceOwner->data->oauth->token_valid === 'true'
        ) {
            return redirect()->route('dashboard')->withError('You cannot use our services unless you provide the relevant permissions upon login. Please try again.');
        }

        $account = $this->completeLogin($resourceOwner, $accessToken);

        Auth::guard('vatsim-sso')->loginUsingId($account->id);

        return SecondaryLoginController::attemptSecondaryAuth($account);
    }

    protected function completeLogin(object $resourceOwner, object $token)
    {
        $account = Account::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->name_first = $resourceOwner->data->personal->name_first;
        $account->name_last = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();
        $account->is_inactive = null;
        $account->updateVatsimRatings($resourceOwner->data->vatsim->rating->id, $resourceOwner->data->vatsim->pilotrating->id);
        $account->updateDivision($resourceOwner->data->vatsim->division->id, $resourceOwner->data->vatsim->region->id);

        if ($resourceOwner->data->oauth->token_valid) {
            $account->vatsim_access_token = $token->getToken();
            $account->vatsim_refresh_token = $token->getRefreshToken();
            $account->vatsim_token_expires = $token->getExpires();
        }

        $account->save();

        return $account;
    }
}
