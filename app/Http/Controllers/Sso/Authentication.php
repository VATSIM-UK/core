<?php

namespace App\Http\Controllers\Sso;

use Auth;
use Input;
use Request;
use Redirect;
use App\Models\Sso\Token;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Authentication extends \App\Http\Controllers\BaseController
{
    public function getLogin()
    {
        // Did we receive a token?  If we didn't get rid of them!
        if (!Input::get('token', false)) {
            die('SOME GENERIC ERROR');
        }

        // Check expired/invalid
        $ssoToken = Input::get('token');
        try {
            $ssoToken = Token::where('token', '=', $ssoToken)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            die('TOKEN NOT FOUND');
        }

        // Got the token, let's do something.
        if (!Request::query('return')) {
            // Let's extend the expiry of this token for a minute or two....
            $ssoToken->expires_at = \Carbon\Carbon::now('GMT')->addMinutes(60)->toDateTimeString();
            $ssoToken->save();

            // Now let's send them off to the login shizzle!
            return Redirect::to('/mship/auth/login?returnURL='.urlencode(url('/sso/auth/login?token='.Request::query('token').'&return=1')).'&force='.Request::query('force', 0));
        } else {
            // We're successfully authenticated it seems... We can now return the access token.
            $ssoToken->account_id = $this->account->id;
            $ssoToken->expires_at = \Carbon\Carbon::now('GMT')->addSeconds(30)->toDateTimeString();
            $ssoToken->save();

            return Redirect::to($ssoToken->return_url);
        }
    }
}
