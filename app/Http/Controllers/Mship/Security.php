<?php

namespace App\Http\Controllers\Mship;

use Auth;
use Illuminate\Http\Request;
use Input;
use Session;
use Redirect;
use Carbon\Carbon;
use App\Models\Sys\Token;
use App\Models\Sys\Token as SystemToken;
use App\Exceptions\Mship\DuplicatePasswordException;
use App\Notifications\Mship\Security\TemporaryPassword;
use App\Notifications\Mship\Security\ForgottenPasswordLink;

class Security extends \App\Http\Controllers\BaseController
{
    public function getEnable()
    {
        return Redirect::route('mship.security.replace');
    }

    public function getReplace($disable = false)
    {
        if ($disable && Auth::user()->mandatory_password) {
            return Redirect::route('mship.manage.dashboard')->with('error', 'You cannot disable your secondary password.');
        } elseif ($disable && !Auth::user()->hasPassword()) {
            $disable = false;
        } elseif ($disable) {
            $this->setTitle('Disable');
        }

        if (!Auth::user()->hasPassword()) {
            $this->setTitle('Create');
            if (Auth::user()->mandatory_password) {
                $slsType = 'forced';
            } else {
                $slsType = 'requested';
            }
        } else {
            if (Auth::user()->hasPasswordExpired()) {
                $slsType = 'expired';
            } elseif (!$disable) {
                $slsType = 'replace';
                $this->setTitle('Replace');
            } else {
                $slsType = 'disable';
                $this->setTitle('Disable');
            }
        }

        return $this->viewMake('mship.security.replace')->with('sls_type', $slsType)->with('disable', $disable);
    }

    public function postReplace(Request $request, $disable = false)
    {
        if ($disable && Auth::user()->mandatory_password) {
            return Redirect::route('mship.manage.dashboard')->with('error', 'You cannot disable your secondary password.');
        }

        if (Auth::user()->hasPassword()) {
            if (!Auth::user()->verifyPassword(Input::get('old_password'))) {
                return Redirect::route('mship.security.replace', [(int) $disable])->with('error', 'Your old password is incorrect.  Please try again.');
            }

            if ($disable) {
                Auth::user()->removePassword();

                return Redirect::route('mship.manage.dashboard')->with('success', 'Your secondary password has been deleted successfully.');
            }
        }

        // Check passwords match.
        if (Input::get('new_password') != Input::get('new_password2')) {
            return Redirect::route('mship.security.replace')->with('error', 'The two passwords you enter did not match - you must enter your desired password, twice.');
        }
        $newPassword = Input::get('new_password');

        // Check the minimum length first.
        if (strlen($newPassword) < 6) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password length must be at least 6 characters)');
        }

        // Check the number of alphabetical characters.
        if (preg_match_all('/[a-zA-Z]/', $newPassword) < 3) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least 3 alphabetical characters)');
        }

        // Check the number of numeric characters.
        if (preg_match_all('/[0-9]/', $newPassword) < 1) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least one number)');
        }

        // All requirements met, set the password!
        try {
            Auth::user()->setPassword($newPassword);
        } catch (DuplicatePasswordException $e) {
            return Redirect::route('mship.security.replace')->with('error', 'Your new password cannot be the same as your old password.');
        }

        Session::put('auth.secondary', Carbon::now());
        $request->session()->put([
            'password_hash' => $request->user()->getAuthPassword(),
        ]);

        return redirect()->route('default')->withSuccess('Password reset successfully.');
    }
}
