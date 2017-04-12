<?php

namespace App\Http\Controllers\Mship;

use Auth;
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
    public function getAuth()
    {
        if (Session::has('auth_override')) {
            return Redirect::route('mship.auth.redirect');
        }

        // Let's check whether we even NEED this.
        if (Session::has('auth_extra') || !Auth::user()->hasPassword()) {
            return Redirect::route('mship.auth.redirect');
        }

        // Next, do we need to replace/reset?
        if (Auth::user()->hasPasswordExpired()) {
            return Redirect::route('mship.security.replace');
        }

        // So we need it.  Let's go!
        return $this->viewMake('mship.security.auth');
    }

    public function postAuth()
    {
        if (Auth::user()->verifyPassword(Input::get('password'))) {
            Session::put('auth_extra', Carbon::now());

            return Redirect::route('mship.auth.redirect');
        }

        return Redirect::route('mship.security.auth')->with('error', 'Invalid password entered - please try again.');
    }

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

    public function postReplace($disable = false)
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

        Session::put('auth_extra', Carbon::now());

        return Redirect::route('mship.security.auth');
    }

    public function getForgotten()
    {
        if (!Auth::user()->hasPassword()) {
            return Redirect::route('mship.manage.dashboard');
        }

        $generatedToken = Token::generate('mship_account_security_reset', false, $this->account);
        $this->account->notify(new ForgottenPasswordLink($generatedToken));

        Auth::logout();

        return $this->viewMake('mship.security.forgotten')->with('success', trans('mship.security.forgotten.success').'<br />'.trans('general.dialog.youcanclose'));
    }

    public function getForgottenLink($code = null)
    {
        // Search tokens for this code!
        $token = SystemToken::where('code', '=', $code)->valid()->first();

        // Is it valid? Has it expired? Etc?
        if (!$token) {
            return $this->viewMake('mship.security.forgotten')->with('error', 'You have provided an invalid password reset token.');
        }

        // Is it valid? Has it expired? Etc?
        if ($token->is_used) {
            return $this->viewMake('mship.security.forgotten')->with('error', 'You have provided an invalid password reset token.');
        }

        // Is it valid? Has it expired? Etc?
        if ($token->is_expired) {
            return $this->viewMake('mship.security.forgotten')->with('error', 'You have provided an invalid password reset token.');
        }

        // Is it related and for the right thing?!
        if (!$token->related or $token->type != 'mship_account_security_reset') {
            return $this->viewMake('mship.security.forgotten')->with('error', 'You have provided an invalid password reset token.');
        }

        // Let's now consume this token.
        $token->consume();

        $temporaryPassword = str_random(12);
        $this->account->setPassword($temporaryPassword, true);
        $this->account->notify(new TemporaryPassword($temporaryPassword));

        Auth::logout();

        return $this->viewMake('mship.security.forgotten')
            ->with('success', 'A new password has been generated for you and emailed to your <strong>primary</strong> '
                .'VATSIM email.<br /> You can now close this window.');
    }
}
