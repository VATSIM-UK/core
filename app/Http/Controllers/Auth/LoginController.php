<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
        if (!$request->has('code') || !$request->has('state')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $request->session()->put('vatsimauthstate', $this->provider->getState());
            return redirect()->away($authorizationUrl);
        }

        if ($request->input('state') !== session()->pull('vatsimauthstate')) {
            return redirect()->route('dashboard')->withError("Something went wrong, please try again.");
        }

        return $this->verifyLogin($request);
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException $e) {
            return redirect()->route('dashboard')->withError("Something went wrong, please try again.");
        }

        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        if (!
        (isset($resourceOwner->data) &&
            isset($resourceOwner->data->cid) &&
            isset($resourceOwner->data->personal->name_first) &&
            isset($resourceOwner->data->personal->name_last) &&
            isset($resourceOwner->data->personal->email) &&
            $resourceOwner->data->oauth->token_valid === "true")
        ) {
            return redirect()->route('dashboard')->withError("You cannot use our services unless you provide the relevant permissions upon login. Please try again.");
        }

        $account = $this->completeLogin($resourceOwner, $accessToken);

        Auth::guard('vatsim-sso')->loginUsingId($account->id);

        return SecondaryLoginController::attemptSecondaryAuth($account);
    }

    public function logout()
    {
        auth()->logout();

        return redirect(route('site.home'));
    }

    protected function completeLogin($resourceOwner, $token)
    {
        $account = Account::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->name_first = $resourceOwner->data->personal->name_first;
        $account->name_last = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();

        if ($resourceOwner->data->oauth->token_valid) {
            $account->vatsim_access_token = $token->getToken();
            $account->vatsim_refresh_token = $token->getRefreshToken();
            $account->vatsim_token_expires = $token->getExpires();
        }

        $account->save();

        // New SSO does not provide us with any other member info (e.g. ratings)
        // so we'll need to fetch it from AutoTools or the API.
        $this->updateAccount($account);

        return $account;
    }

    private function updateAccount(Account $account)
    {
        try {
            (new UpdateMember($account->id))->handle();
        } catch (\Exception $e) {
            // Service likely unavailable, let the user continue with login.
        }
    }
}
