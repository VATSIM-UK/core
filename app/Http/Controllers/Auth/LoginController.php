<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $this->middleware('guest')->except('showLoginForm', 'loginSecondary', 'logout');
    }

    public function getLogin()
    {
        if (Auth::guard('vatsim-sso')->check() && !Auth::check()) {
            return $this->attemptSecondaryAuth();
        } else {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function loginMain(Request $request)
    {

        // user has not been authenticated with VATSIM SSO
        if (!Auth::guard('vatsim-sso')->check()) {
            return $this->attemptVatsimAuth();
        }

        if (!Auth::check()) {
            $this->attemptSecondaryAuth();
        }

        return redirect()->intended(route('mship.manage.dashboard'));
    }

    protected function getVatsimAuth()
    {
        return Auth::guard('vatsim-sso')->user()->id;
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
        }
        // Check if there was a CURL error code
        if (VATSIMSSO::error()['code']) {
            Log::error('VATSIMSSO was unable to reach CERT. Code:'.VATSIMSSO::error()['code'].' Message:'.VATSIMSSO::error()['message']);
            return redirect()->back()->withErrors(['connection' => "We were unable to contact VATSIM's certification service. Please try again later. If this persists, please contact Web Services."]);
        }
        throw new \Exception('SSO failed: '.VatsimSSO::error()['message']);
    }

    protected function setVatsimAuth($userId)
    {
        Auth::guard('vatsim-sso')->loginUsingId($userId);
    }

    protected function attemptSecondaryAuth()
    {
        $member = Auth::guard('vatsim-sso')->user();

        if ($member->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        $intended = Session::pull('url.intended', route('site.home'));

        Auth::login(Auth::guard('vatsim-sso')->user(), true);

        return redirect($intended);
    }

    public function loginSecondary(Request $request)
    {
        if (!Auth::guard('vatsim-sso')->check()) {
            return redirect()->route('dashboard')
                ->withError('Could not authenticate: VATSIM.net authentication is not present.');
        }

        Auth::shouldUse('web');
        $response = $this->login($request);

        return $response;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return ['id' => Auth::guard('vatsim-sso')->user()->id, 'password' => $request->input('password')];
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
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
        $account = Account::firstOrNew(['id' => $user->id]);
        $account->name_first = utf8_decode($user->name_first);
        $account->name_last = utf8_decode($user->name_last);
        $account->email = $user->email;
        $account->experience = $user->experience;
        $account->joined_at = $user->reg_date;
        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();
        $account->is_inactive = $user->rating->id == -1 ? true : false;
        $account->updateVatsimRatings($user->rating->id, $user->pilot_rating->rating);
        $account->updateDivision($user->division->code, $user->region->code);
        $account->save();

        if (!is_numeric($user->rating->id) || !is_numeric($user->pilot_rating->rating)) {
            $job = new UpdateMember($user);
            $this->dispatch($job);
        }

        $this->setVatsimAuth($user->id);

        return $this->attemptSecondaryAuth();
    }

    public function vSsoValidationFailure($error)
    {
        return redirect()->route('dashboard')->withError('Could not authenticate: '.$error['message']);
    }
}
