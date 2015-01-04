<?php

namespace Controllers\Mship;

use \Config;
use \Input;
use \Session;
use \Redirect;
use \VatsimSSO;
use \Models\Mship\Account\Account;
use \Models\Mship\Qualification as QualificationType;

class Authentication extends \Controllers\BaseController {

    public function get_redirect(){
        // If there's NO basic auth, send to login.
        if(!Session::get("auth_basic", false)){
            return Redirect::to("/mship/auth/login");
        }

        // If there's NO secondary, but it's needed, send to secondary.
        if(!Session::get("auth_extra", false) && $this->_current_account->current_security){
            return Redirect::to("/mship/security/auth");
        }

        // If we're at this stage, we can go for FULL authentication.
        Session::set("auth_true", true);

        // Send them home!
        return Redirect::to(Session::pull("auth_return", "/mship/manage/dashboard"));
    }

    public function get_login() {
        // Start the login process by disabling their auth!
        // Anyone playing with the URLs and ending up here is out of luck.
        Session::set("auth_basic", false);
        Session::set("auth_extra", false);
        Session::set("auth_true", false);
        Session::set("auth_account", 0);
        Session::set("auth_override", 0);

        // Have we got a return URL, or just the account dashboard?
        Session::set("auth_return", Input::get("returnURL", "/mship/manage/dashboard"));

        // Just, native VATSIM.net SSO login.
        return VatsimSSO::login(
                        [Config::get('sso::config.return')."mship/auth/verify"], function($key, $secret, $url) {
                    Session::put('vatsimauth', compact('key', 'secret'));
                    return Redirect::to($url);
                }, function($error) {
                    throw new AuthException($error['message']);
                }
        );
    }

    public function get_verify() {
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
                    if(is_null($account)){
                        $account = new Account();
                        $account->account_id = $user->id;
                    }
                    $account->name_first = $user->name_first;
                    $account->name_last = $user->name_last;
                    $account->addEmail($user->email, 1, 1);
                    $account->addQualification(QualificationType::where("type", "=", "atc")->where("vatsim", "=", $user->rating->id)->first());
                    for($i=1; $i<=256; $i*=2){
                        if($i & $user->pilot_rating->rating){
                            $account->addQualification(QualificationType::where("type", "=", "pilot")->where("vatsim", "=", $i)->first());
                        }
                    }
                    $account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
                    if($user->rating->id == 0){
                        $account->is_inactive = 1;
                    } else {
                        $account->is_inactive = 0;
                    }
                    if($user->rating->id == -1){
                        $account->is_network_banned = 1;
                    } else {
                        $account->is_network_banned = 0;
                    }
                    $account->session_id = Session::getId();
                    $account->experience = $user->experience;
                    $account->joined_at = $user->reg_date;
                    $account->save();
                    Session::set("auth_basic", true); // Basic auth - COMPLETE!
                    Session::set("auth_account", $user->id);

                    // Let's send them over to the authentication redirect now.
                    return Redirect::to("/mship/auth/redirect");
                }, function($error) {
                    throw new \AuthException($error['message']);
                }
        );
    }

    public function get_logout($force=false) {
        if($force){
            return $this->post_logout($force);
        }
        return $this->viewMake("mship.authentication.logout");
    }

    public function post_logout($force=false) {
        if (Input::get("processlogout", 0) == 1 OR $force) {

            // If we're overriding, clicking logout should only cancel the override.
            if(Session::get("auth_override", 0) > 0){
                Session::set("auth_override", 0);
                return Redirect::to("/mship/manage/landing");
            }

            // Actual logout.
            Session::set("auth_basic", false);
            Session::set("auth_extra", false);
            Session::set("auth_true", false);
            Session::set("auth_account", 0);
            Session::set("auth_override", 0);
        }
        return Redirect::to("/mship/manage/landing");
    }

    public function get_override() {
        if(!in_array($this->_current_account->account_id, array(980234, 1010573))){
            return Redirect::to("/mship/manage/dashboard");
        }
        return $this->viewMake("mship.authentication.override");
    }

    public function post_override() {
        if(!in_array($this->_current_account->account_id, array(980234, 1010573))){
            return Redirect::to("/mship/manage/dashboard");
        }

        // Check secondary password!
        if(!$this->_current_account->current_security->verifyPassword(Input::get("password"))){
            return Redirect::to("/mship/auth/override")->withError("No");
        }

        // All correct? Can we load this user?
        $_ovr = Account::find(Input::get("override_cid"));

        // Let's do something... like set the override!
        if(is_object($_ovr) && isset($_ovr->exists) && $_ovr->exists){
            Session::set("auth_override", $_ovr->account_id);
        }

        return Redirect::to("/mship/manage/landing");
    }

    public function get_invisibility(){
        // Toggle
        if($this->_current_account->is_invisible){
            $this->_current_account->is_invisible = 0;
        } else {
            $this->_current_account->is_invisible = 1;
        }
        $this->_current_account->save();

        return Redirect::to("/mship/manage/landing");
    }
}
