<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Middleware\RedirectToIntended;
use App\Models\Mship\Account;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Session;
use VatsimSSO;
use App\Exceptions\Mship\DuplicateStateException;
use App\Models\Mship\Qualification as QualificationType;
use App\Exceptions\Mship\DuplicateQualificationException;

/**
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen. The controller uses a trait
 * to conveniently provide its functionality to your applications.
 *
 * @package App\Http\Controllers\Auth
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
            Session::put('cert_offline', true);

            return redirect()->route('mship.auth.loginAlternative')->withError(VatsimSSO::error()['message']);
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
        $account = Account::find($user->id);
        if (is_null($account)) {
            $account = new Account();
            $account->id = $user->id;
        }
        $account->name_first = $user->name_first;
        $account->name_last = $user->name_last;
        $account->email = $user->email;

        try {
            // Sort the ATC Rating out.
            $atcRating = $user->rating->id;
            if ($atcRating > 7) {
                // Store the admin/ins rating.
                $qualification = QualificationType::parseVatsimATCQualification($atcRating);
                if (!is_null($qualification)) {
                    $account->addQualification($qualification);
                }

                $atcRatingInfo = \VatsimXML::getData($user->id, 'idstatusprat');
                if (isset($atcRatingInfo->PreviousRatingInt)) {
                    $atcRating = $atcRatingInfo->PreviousRatingInt;
                }
            }

            $parsedRating = QualificationType::parseVatsimATCQualification($atcRating);

            if ($parsedRating) {
                $account->addQualification($parsedRating);
            }

            for ($i = 1; $i <= 256; $i *= 2) {
                if ($i & $user->pilot_rating->rating) {
                    $account->addQualification(QualificationType::ofType('pilot')->networkValue($i)->first());
                }
            }
        } catch (DuplicateQualificationException $e) {
            // TODO: Something.
        }

        try {
            $state = determine_mship_state_from_vatsim($user->region->code, $user->division->code);
            $account->addState($state, $user->region->code, $user->division->code);
        } catch (DuplicateStateException $e) {
            // TODO: Something.
        }

        $account->last_login = Carbon::now();
        $account->last_login_ip = \Request::ip();
        if ($user->rating->id == -1) {
            $account->is_inactive = 1;
        } else {
            $account->is_inactive = 0;
        }

        // Are they network banned, but unbanned in our system?
        // Add it!
        if ($user->rating->id == 0 && $account->is_network_banned === false) {
            // Add a ban.
            $newBan = new \App\Models\Mship\Account\Ban();
            $newBan->type = \App\Models\Mship\Account\Ban::TYPE_NETWORK;
            $newBan->reason_extra = 'Network ban discovered via Cert login.';
            $newBan->period_start = Carbon::now();
            $newBan->save();

            $account->bans()->save($newBan);
        }

        // Are they banned in our system (for a network ban) but unbanned on the network?
        // Then expire the ban.
        if ($account->is_network_banned === true && $user->rating->id > 0) {
            $ban = $account->network_ban;
            $ban->period_finish = Carbon::now();
            $ban->save();
        }

        // Session stuff.
        $account->session_id = Session::getId();
        $account->experience = $user->experience;
        $account->joined_at = $user->reg_date;
        $account->save();

        $this->setVatsimAuth($user->id);
        Session::forget('auth.secondary');

        return $this->attemptSecondaryAuth();
    }

    public function vSsoValidationFailure($error) {
        return redirect()->route('default')->withError('Could not authenticate: ' . $error['message']);
    }
}
