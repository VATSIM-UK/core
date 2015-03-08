<?php

namespace Controllers\Mship;

use \Config;
use \Auth;
use \URL;
use \Input;
use \Session;
use \Redirect;
use \VatsimSSO;
use \Models\Mship\Account;
use \Models\Mship\Qualification as QualificationType;

class Authentication extends \Controllers\BaseController {

    public function getRedirect() {
        // If there's NO basic auth, send to login.
        if (!Auth::user()->check()) {
            return Redirect::route("mship.auth.login");
        }

        // If there's NO secondary, but it's needed, send to secondary.
        if (!Auth::user()->get()->auth_extra && Auth::user()->get()->current_security && !Session::has("auth_override")) {
            return Redirect::route("mship.security.auth");
        }

        // What about if there's secondary, but it's expired?
        if (!Session::has("auth_override") && Auth::user()->get()->auth_extra && (Auth::user()->get()->auth_extra_at == NULL OR Auth::user()->get()->auth_extra_at->addHours(4)->isPast())) {
            $user = Auth::user()->get();
            $user->auth_extra = 0;
            $user->save();
            return Redirect::route("mship.auth.redirect");
        }

        // Send them home!
        return Redirect::to(Session::pull("auth_return", URL::route("mship.manage.dashboard")));
    }

    public function getLoginAlternative() {
        if (!Session::has("cert_offline")) {
            return Redirect::route("mship.auth.login");
        }

        // Display an alternative login form.
        $this->_pageTitle = "Alternative Login";
        return $this->viewMake("mship.authentication.login_alternative");
    }

    public function postLoginAlternative() {
        if (!Session::has("cert_offline")) {
            return Redirect::route("mship.auth.login");
        }

        if (!Input::get("cid", false) OR ! Input::get("password", false)) {
            return Redirect::route("mship.auth.loginAlternative")->withError("You must enter a cid and password.");
        }

        // Let's find the member.
        $account = Account::find(Input::get("cid"));

        if (!$account) {
            return Redirect::route("mship.auth.loginAlternative")->withError("You must enter a valid cid and password combination.");
        }

        // Let's get their current security and verify...
        if (!$account->current_security OR !$account->current_security->verifyPassword(Input::get("password"))) {
            return Redirect::route("mship.auth.loginAlternative")->withError("You must enter a valid cid and password combination.");
        }

        // We're in!
        // Let's do lots of logins....
        $account->last_login = \Carbon\Carbon::now();
        $account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
        $account->auth_extra = 1;
        $account->auth_extra_at = \Carbon\Carbon::now();
        $account->save();

        Auth::user()->login($account, true);
        Session::forget("cert_offline");

        // Let's send them over to the authentication redirect now.
        return Redirect::route("mship.auth.redirect");
    }

    public function getLogin() {
        Session::set("auth_return", Input::get("returnURL", URL::route("mship.manage.dashboard")));

        // Do we already have some kind of CID? If so, we can skip this bit and go to the redirect!
        if (Auth::user()->check() OR Auth::user()->viaremember()) {
            return Redirect::route("mship.auth.redirect");
        }

        // Just, native VATSIM.net SSO login.
        return VatsimSSO::login(
                        [URL::route("mship.auth.verify"), "suspended" => true, "inactive" => true], function($key, $secret, $url) {
                    Session::put('vatsimauth', compact('key', 'secret'));
                    return Redirect::to($url);
                }, function($error) {
                    Session::set("cert_offline", true);
                    return Redirect::route("mship.auth.loginAlternative");
                }
        );
    }

    public function getVerify() {
        if (!Session::has('vatsimauth')) {
            throw new \AuthException('Session does not exist');
        }

        $session = Session::get('vatsimauth');

        if (Input::get('oauth_token') !== $session['key']) {
            throw new \AuthException('Returned token does not match');
        }

        if (!Input::has('oauth_verifier')) {
            throw new \AuthException('No verification code provided');
        }

        return VatsimSSO::validate($session['key'], $session['secret'], Input::get('oauth_verifier'), function($user, $request) {
                    Session::forget('vatsimauth');

                    // At this point WE HAVE data in the form of $user;
                    $account = Account::find($user->id);
                    if (is_null($account)) {
                        $account = new Account();
                        $account->account_id = $user->id;
                    }
                    $account->name_first = $user->name_first;
                    $account->name_last = $user->name_last;
                    $account->addEmail($user->email, true, true);

                    // Sort the ATC Rating out.
                    $atcRating = $user->rating->id;
                    if ($atcRating > 7) {
                        // Store the admin/ins rating.
                        if ($atcRating >= 11) {
                            $account->addQualification(QualificationType::ofType("admin")->networkValue($atcRating)->first());
                        } else {
                            $account->addQualification(QualificationType::ofType("training_atc")->networkValue($atcRating)->first());
                        }

                        $atcRatingInfo = \VatsimXML::getData($user->id, "idstatusprat");
                        if (isset($atcRatingInfo->PreviousRatingInt)) {
                            $atcRating = $atcRatingInfo->PreviousRatingInt;
                        }
                    }
                    $account->addQualification(QualificationType::ofType("atc")->networkValue($atcRating)->first());

                    for ($i = 1; $i <= 256; $i*=2) {
                        if ($i & $user->pilot_rating->rating) {
                            $account->addQualification(QualificationType::ofType("pilot")->networkValue($i)->first());
                        }
                    }

                    $account->determineState($user->region->code, $user->division->code);

                    $account->last_login = \Carbon\Carbon::now();
                    $account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
                    if ($user->rating->id == 0) {
                        $account->is_inactive = 1;
                    } else {
                        $account->is_inactive = 0;
                    }
                    if ($user->rating->id == -1) {
                        $account->is_network_banned = 1;
                    } else {
                        $account->is_network_banned = 0;
                    }
                    $account->session_id = Session::getId();
                    $account->experience = $user->experience;
                    $account->joined_at = $user->reg_date;
                    $account->auth_extra = 0;
                    $account->determineState($user->region->code, $user->division->code);
                    $account->save();

                    Auth::user()->login($account, true);

                    // Let's send them over to the authentication redirect now.
                    return Redirect::route("mship.auth.redirect");
                }, function($error) {
                    throw new \AuthException($error['message']);
                }
        );
    }

    public function getLogout($force = false) {
        Session::set("logout_return", Input::get("returnURL", "/mship/manage/dashboard"));

        if ($force) {
            return $this->postLogout($force);
        }
        return $this->viewMake("mship.authentication.logout");
    }

    public function postLogout($force = false) {
        if (Auth::user()->check() && (Input::get("processlogout", 0) == 1 OR $force)) {
            $user = Auth::user()->get();
            $user->auth_extra = 0;
            $user->auth_extra_at = NULL;
            $user->save();
            Auth::user()->logout();
        }
        return Redirect::to(Session::pull("logout_return", "/mship/manage/landing"));
    }

    public function getOverride() {
        if (!in_array(Auth::user()->get()->account_id, array(980234, 1010573))) {
            return Redirect::route("mship.manage.dashboard");
        }
        return $this->viewMake("mship.authentication.override");
    }

    public function postOverride() {
        if (!in_array(Auth::user()->get()->account_id, array(980234, 1010573))) {
            return Redirect::route("mship.manage.dashboard");
        }

        // Check secondary password!
        if (!Auth::user()->get()->current_security->verifyPassword(Input::get("password"))) {
            return Redirect::route("mship.auth.override")->withError("No");
        }

        // All correct? Can we load this user?
        $_ovr = Account::find(Input::get("override_cid"));

        // Let's do something... like set the override!
        if (is_object($_ovr) && isset($_ovr->exists) && $_ovr->exists) {
            Session::set("auth_override", $_ovr->account_id);
        }

        return Redirect::route("mship.manage.landing");
    }

    public function getInvisibility() {
        // Toggle
        if (Auth::user()->get()->is_invisible) {
            Auth::user()->get()->is_invisible = 0;
        } else {
            Auth::user()->get()->is_invisible = 1;
        }
        Auth::user()->get()->save();

        return Redirect::route("mship.manage.landing");
    }

}
