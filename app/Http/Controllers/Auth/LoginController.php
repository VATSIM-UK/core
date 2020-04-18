<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\VatsimOAuthController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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

    public function mainLogin(Request $request)
    {
        $this->login($request);
    }

    public function login(Request $request)
    {
        if (!$request->has('code') || !$request->has('state')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl(); // Generates state
            $request->session()->put('vatsimauthstate', $this->provider->getState());
            return redirect()->away($authorizationUrl);
        } elseif ($request->input('state') !== session()->pull('vatsimauthstate')) {
            return redirect()->route('dashboard')->withError("Something went wrong, please try again (state mismatch).");
        } else {
            return $this->verifyLogin($request);
        }
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException $e) {
            return redirect()->route('dashboard')->withError("Something went wrong, please try again later.");
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
            return redirect()->route('dashboard')->withError("We need you to grant us all marked permissions");
        }

        $account = $this->completeLogin($resourceOwner, $accessToken);

        Auth::guard('vatsim-sso')->loginUsingId($account->id);

        return SecondaryLoginController::attemptSecondaryAuth();
    }

    protected function completeLogin($resourceOwner, $token)
    {
        $account = Account::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->name_first = $resourceOwner->data->personal->name_first;
        $account->name_last = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();

        if ($resourceOwner->data->oauth->token_valid) { // User has given us permanent access to updated data
            $account->vatsim_access_token = $token->getToken();
            $account->vatsim_refresh_token = $token->getRefreshToken();
            $account->vatsim_token_expires = $token->getExpires();
        }

        $account->save();

        $this->updateMember($account);

        return $account;
    }

    public function logout()
    {
        auth()->logout();

        return redirect(route('site.home'));
    }

    private function updateMember(Account $account)
    {
        $job = new UpdateMember($account);
        return $this->dispatch($job);
    }
}
