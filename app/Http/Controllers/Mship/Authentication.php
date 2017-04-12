<?php

namespace App\Http\Controllers\Mship;

use URL;
use Auth;
use Input;
use Request;
use Session;
use Redirect;
use VatsimSSO;
use Carbon\Carbon;
use App\Models\Mship\Account;
use App\Http\Controllers\BaseController;
use App\Exceptions\Mship\DuplicateStateException;
use App\Models\Mship\Qualification as QualificationType;
use App\Exceptions\Mship\DuplicateQualificationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Authentication extends BaseController
{
    public function getRedirect()
    {
        // If there's NO basic auth, send to login.
        if (!Auth::check()) {
            return Redirect::route('mship.auth.login');
        }

        // Has this user logged in from a similar IP as somebody else?
        $check = Account::withIp($this->account->last_login_ip)
                        ->where('last_login', '>=', Carbon::now()->subHours(4))
                        ->where('id', '!=', $this->account->id)
                        ->count();

        if ($check > 0 && !Session::get('auth_duplicate_ip', false)) {
            Session::forget('auth_extra');
            Session::put('auth_duplicate_ip', true);
        }

        // If there's NO secondary, but it's needed, send to secondary.
        if (!Session::has('auth_extra') && $this->account->hasPassword() && !Session::has('auth_override')) {
            return Redirect::route('mship.security.auth');
        }

        // What about if there's secondary, but it's expired?
        if (!Session::has('auth_override')
            && Session::has('auth_extra')
            && Session::get('auth_extra') !== false
            && Session::get('auth_extra')->addHours(4)->isPast()
        ) {
            Session::forget('auth_extra');

            return Redirect::route('mship.auth.redirect');
        }

        // If a secondary is required, but they haven't set one, send them away to set one.
        if ($this->account->mandatory_password && !$this->account->hasPassword()) {
            return Redirect::route('mship.security.replace');
        }

        if (!$this->account->hasPassword()) {
            Session::put('auth_extra', false);
        }

        // Send them home!
        Session::forget('auth_duplicate_ip');

        $returnURL = Session::pull('auth_return', URL::route('mship.manage.dashboard'));
        if ($returnURL == URL::route('mship.manage.dashboard') && ($this->account->has_unread_important_notifications || $this->account->has_unread_must_acknowledge_notifications)) {
            Session::put('force_notification_read_return_url', $returnURL);
            $returnURL = URL::route('mship.notification.list');
        }

        return Redirect::to($returnURL);
    }

    public function getLoginAlternative()
    {
        if (!Session::has('cert_offline')) {
            return Redirect::route('mship.auth.login');
        }

        // Display an alternative login form.
        $this->pageTitle = 'Alternative Login';

        return $this->viewMake('mship.authentication.login_alternative');
    }

    public function postLoginAlternative()
    {
        if (!Session::has('cert_offline')) {
            return Redirect::route('mship.auth.login');
        }

        if (!Input::get('cid', false) || !Input::get('password', false)) {
            return Redirect::route('mship.auth.loginAlternative')->withError('You must enter a cid and password.');
        }

        // Let's find the member.
        $account = Account::find(Input::get('cid'));

        if (!$account) {
            return Redirect::route('mship.auth.loginAlternative')->withError('You must enter a valid cid and password combination.');
        }

        // Let's get their current security and verify...
        if (!$account->hasPassword() || !$account->verifyPassword(Input::get('password'))) {
            return Redirect::route('mship.auth.loginAlternative')->withError('You must enter a valid cid and password combination.');
        }

        // We're in!
        // Let's do lots of logins....
        $account->last_login = Carbon::now();
        $account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
        Session::put('auth_extra', Carbon::now());
        $account->save();

        Auth::login($account, true);

        Session::forget('cert_offline');

        // Let's send them over to the authentication redirect now.
        return Redirect::route('mship.auth.redirect');
    }

    public function getLogin()
    {
        if (!Session::has('auth_return')) {
            Session::put('auth_return', Input::get('returnURL', URL::route('mship.manage.dashboard')));
        }

        // Do we already have some kind of CID? If so, we can skip this bit and go to the redirect!
        if (Auth::check() || Auth::viaRemember()) {
            // Let's just check we're not demanding forceful re-authentication via secondary!
            if (Request::query('force', false)) {
                Session::forget('auth_extra');
            }

            return Redirect::route('mship.auth.redirect');
        }

        // Just, native VATSIM.net SSO login.
        return VatsimSSO::login(
            [URL::route('mship.auth.verify'), 'suspended' => true, 'inactive' => true],
            function ($key, $secret, $url) {
                Session::put('vatsimauth', compact('key', 'secret'));

                return Redirect::to($url);
            },
            function ($error) {
                Session::put('cert_offline', true);

                return Redirect::route('mship.auth.loginAlternative');
            }
        );
    }

    public function getVerify()
    {
        if (Input::get('oauth_cancel') !== null) {
            return Redirect::away('http://vatsim-uk.co.uk');
        }

        if (!Session::has('vatsimauth')) {
            throw new NotFoundHttpException();
        }

        $session = Session::get('vatsimauth');

        if (Input::get('oauth_token') !== $session['key']) {
            throw new \Illuminate\Auth\AuthenticationException('Returned token does not match');
        }

        if (!Input::has('oauth_verifier')) {
            throw new \Illuminate\Auth\AuthenticationException('No verification code provided');
        }

        return VatsimSSO::validate($session['key'], $session['secret'], Input::get('oauth_verifier'), function ($user, $request) {
            Session::forget('vatsimauth');

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
            $account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
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

            Session::forget('auth_extra');

            Auth::login($account, true);

            // Let's send them over to the authentication redirect now.
            return Redirect::route('mship.auth.redirect');
        }, function ($error) {
            throw new \Illuminate\Auth\AuthenticationException($error['message']);
        });
    }

    public function getLogout($force = false)
    {
        Session::put('logout_return', Input::get('returnURL', '/mship/manage/dashboard'));

        if ($force) {
            return $this->postLogout($force);
        }

        return $this->viewMake('mship.authentication.logout');
    }

    public function postLogout($force = false)
    {
        if (Auth::check() && (Input::get('processlogout', 0) == 1 or $force)) {
            Session::forget('auth_extra');
            Auth::logout();
        }

        return Redirect::to(Session::pull('logout_return', '/mship/manage/landing'));
    }

    public function getInvisibility()
    {
        // Toggle
        if (Auth::user()->is_invisible) {
            Auth::user()->is_invisible = 0;
        } else {
            Auth::user()->is_invisible = 1;
        }
        Auth::user()->save();

        return Redirect::route('mship.manage.landing');
    }
}
