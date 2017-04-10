<?php

namespace App\Http\Controllers\Adm;

use URL;
use Auth;
use Input;
use Session;
use Redirect;
use Response;
use VatsimSSO;
use App\Models\Mship\Account;
use Illuminate\Auth\AuthenticationException;

class Authentication extends \App\Http\Controllers\Adm\AdmController
{
    public function getLogin()
    {
        return $this->viewMake('adm.authentication.login');
    }

    public function getLogout()
    {
        Auth::logout();

        return Redirect::route('adm.authentication.login');
    }

    public function postLogin()
    {
        // Just, native VATSIM.net SSO login.
        return VatsimSSO::login(
            [URL::route('adm.authentication.verify')],
            function ($key, $secret, $url) {
                Session::put('vatsimauth', compact('key', 'secret'));

                return Redirect::to($url);
            },
            function ($error) {
                // TODO: LOG
                throw new AuthenticationException($error['message']);
            }
        );
    }

    public function getVerify()
    {
        if (!Session::has('vatsimauth')) {
            throw new AuthenticationException('Session does not exist');
        }

        $session = Session::get('vatsimauth');

        if (Input::get('oauth_token') !== $session['key']) {
            // TODO: LOG
            throw new AuthenticationException('Returned token does not match');
        }

        if (!Input::has('oauth_verifier')) {
            // TODO: LOG
            throw new AuthenticationException('No verification code provided');
        }

        return VatsimSSO::validate($session['key'], $session['secret'], Input::get('oauth_verifier'), function ($user, $request) {
            Session::forget('vatsimauth');

            // At this point WE HAVE data in the form of $user;
            $account = Account::find($user->id);

            if (!$account) {
                // TODO: LOG
                return Response::make('Unauthorised', 401);
            }

            Auth::login($account);

            // Let's send them over to the authentication redirect now.
            return Redirect::route('adm.dashboard');
        }, function ($error) {
            // TODO: LOG
            throw new AuthenticationException($error['message']);
        });
    }
}
