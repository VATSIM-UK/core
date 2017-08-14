<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Session;
use VatsimSSO;

/**
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen. The controller uses a trait
 * to conveniently provide its functionality to your applications.
 */
class LoginController extends BaseController
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/mship/manage/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function getLogin()
    {
        if ($this->hasVatsimAuth() && !$this->hasSecondaryAuth()) {
            return $this->attemptSecondaryAuth();
        } else {
            return redirect()->route('default');
        }
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function loginMain(Request $request)
    {
        // user has not been authenticated with VATSIM SSO
        if (!$this->hasVatsimAuth()) {
            return $this->attemptVatsimAuth();
        }

        if (!$this->hasSecondaryAuth()) {
            $this->attemptSecondaryAuth();
        }

        return redirect()->intended(route('mship.manage.dashboard'));
    }

    protected function hasVatsimAuth()
    {
        return Session::has('auth.vatsim-sso');
    }

    protected function getVatsimAuth()
    {
        return Session::get('auth.vatsim-sso');
    }

    protected function attemptVatsimAuth()
    {
        $allowSuspended = true;
        $allowInactive = true;

        $token = VatsimSSO::requestToken(route('auth-vatsim-sso'), $allowSuspended, $allowInactive);
        if ($token) {
            $key = $token->token->oauth_token;
            $secret = $token->token->oauth_token_secret;
            Session::put('credentials.vatsim-sso', compact('key', 'secret'));

            return redirect()->to(VatsimSSO::sendToVatsim());
        } else {
            throw new \Exception('SSO failed: '.VatsimSSO::error()['message']);
//            Session::put('cert_offline', true);
//
//            return redirect()->route('mship.auth.loginAlternative')->withError(VatsimSSO::error()['message']);
        }
    }

    protected function setVatsimAuth($userId)
    {
        Session::put('auth.vatsim-sso', $userId);
    }

    protected function hasSecondaryAuth()
    {
        return Session::has('auth.secondary');
    }

    protected function attemptSecondaryAuth()
    {
        $member = Account::find($this->getVatsimAuth());
        if ($member->hasPassword()) {
            return redirect()->route('auth-secondary');
        } else {
            $this->setSecondaryAuth();
            Auth::login(Account::find($this->getVatsimAuth()), true);

            return redirect('/');
        }
    }

    protected function setSecondaryAuth()
    {
        Session::put('auth.secondary', Carbon::now());
    }

    public function loginSecondary(Request $request)
    {
        if (!Session::has('auth.vatsim-sso')) {
            return redirect()->route('default')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        $response = $this->login($request);

        if (Auth::check()) {
            $this->setSecondaryAuth();
        }

        return $response;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return ['id' => $request->session()->get('auth.vatsim-sso'), 'password' => $request->input('password')];
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string',
        ]);
    }

    public function vatsimSsoReturn(Request $request)
    {
        $ssoCredentials = $request->session()->remove('credentials.vatsim-sso');

        $login = VatsimSSO::checkLogin($ssoCredentials['key'], $ssoCredentials['secret'], $request->input('oauth_verifier'));
        if ($login) {
            return $this->vSsoValidationSuccess($login->user, $login->request);
        } else {
            return $this->vSsoValidationFailure(VatsimSSO::error());
        }
    }

    public function vSsoValidationSuccess($user, $request)
    {
        // At this point WE HAVE data in the form of $user;
        $account = Account::firstOrNew(['id' => $user->id]);
        $account->name_first = $user->name_first;
        $account->name_last = $user->name_last;
        $account->email = $user->email;
        $account->experience = $user->experience;
        $account->joined_at = $user->reg_date;
        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();
        $account->is_inactive = $user->rating->id == -1 ? true : false;
        $account->updateVatsimRatings($user->rating->id, $user->pilot_rating->rating);
        $account->updateDivision($user->division->code, $user->region->code);
        $account->save();

        $this->setVatsimAuth($user->id);
        Session::forget('auth.secondary');

        return $this->attemptSecondaryAuth();
    }

    public function vSsoValidationFailure($error)
    {
        return redirect()->route('default')->withError('Could not authenticate: '.$error['message']);
    }
}
