<?php

namespace Controllers\Adm;

use \Auth;
use \Input;
use \Session;
use \Response;
use \URL;
use \View;
use \VatsimSSO;
use \Config;
use \Redirect;
use \Models\Mship\Account;
use \Models\Timeline\Entry;

class Authentication extends \Controllers\Adm\AdmController {

    public function getLogin(){
        return $this->viewMake("adm.authentication.login");
    }

    public function getLogout(){
        Auth::admin()->logout();
        return Redirect::route("adm.authentication.login");
    }

    public function postLogin() {
        // Just, native VATSIM.net SSO login.
        return VatsimSSO::login(
                        [URL::route("adm.authentication.verify")], function($key, $secret, $url) {
                    Session::put('vatsimauth', compact('key', 'secret'));
                    return Redirect::to($url);
                }, function($error) {
                    //Entry::log("EXCEPTION", 0, array("error" => $error['message'], "file" => __FILE__, "method" => "getLogin"));
                    throw new Exception($error['message']);
                }
        );
    }

    public function getVerify() {
        if (!Session::has('vatsimauth')) {
            throw new \Exception('Session does not exist');
        }

        $session = Session::get('vatsimauth');

        if (Input::get('oauth_token') !== $session['key']) {
            //Entry::log("EXCEPTION", 0, array("error" => $error['message'], "file" => __FILE__, "method" => "getVerify"));
            throw new \Exception('Returned token does not match');
        }

        if (!Input::has('oauth_verifier')) {
            //Entry::log("EXCEPTION", 0, array("error" => $error['message'], "file" => __FILE__, "method" => "getVerify"));
            throw new \Exception('No verification code provided');
        }

        return VatsimSSO::validate($session['key'], $session['secret'], Input::get('oauth_verifier'), function($user, $request) {
                    Session::forget('vatsimauth');

                    // At this point WE HAVE data in the form of $user;
                    $account = Account::find($user->id);

                    if(!$account){
                        //Entry::log("LOGIN_FAILURE", $account, array("reason" => "Not authorised to login to the admin centre."));
                        return Response::make("Unauthorised", 401);
                    }

                    Auth::admin()->login($account);

                    // Let's send them over to the authentication redirect now.
                    return Redirect::route("adm.dashboard");
                }, function($error) {
                    //Entry::log("EXCEPTION", 0, array("error" => $error['message'], "file" => __FILE__, "method" => "getVerify"));
                    throw new \Exception($error['message']);
                }
        );
    }
}
