<?php

namespace Controllers\Mship;

use \Redirect;
use \Auth;
use \Session;
use \Input;
use \View;
use \Models\Mship\Account;
use \Models\Mship\Account\Security as AccountSecurity;
use \Models\Mship\Security as SecurityType;
use \Models\Sys\Token as SystemToken;

class Security extends \Controllers\BaseController {

    public function getAuth() {
        if(Session::has("auth_override")){
            return Redirect::route("mship.auth.redirect");
        }

        // Let's check whether we even NEED this.
        if (Auth::user()->get()->auth_extra OR !Auth::user()->get()->current_security OR Auth::user()->get()->current_security == NULL) {
            return Redirect::route("mship.auth.redirect");
        }

        // Next, do we need to replace/reset?
        if (!Auth::user()->get()->current_security->is_active) {
            return Redirect::route("mship.security.replace");
        }

        // So we need it.  Let's go!
        return $this->viewMake("mship.security.auth");
    }

    public function postAuth() {
        if (Auth::user()->get()->current_security->verifyPassword(Input::get("password"))) {
            $user = Auth::user()->get();
            $user->auth_extra = 1;
            $user->auth_extra_at = \Carbon\Carbon::now();
            $user->save();
            return Redirect::route("mship.auth.redirect");
        }
        return Redirect::route("mship.security.auth")->with("error", "Invalid password entered - please try again.");
    }

    public function getEnable() {
        return Redirect::route("mship.security.replace");
    }

    public function getReplace($disable = false) {
        $currentSecurity = Auth::user()->get()->current_security;

        if ($disable && $currentSecurity && !$currentSecurity->security->optional) {
            return Redirect::route("mship.manage.dashboard")->with("error", "You cannot disable your secondary password.");
        } elseif ($disable && !$currentSecurity) {
            $disable = false;
        } elseif($disable) {
            $this->setTitle("Disable");
        }

        if (!$currentSecurity OR $currentSecurity == NULL) {
            $this->setTitle("Create");
            $slsType = 'requested';
        } else {
            if (strlen($currentSecurity->value) < 1) {
                $this->setTitle("Create");
                $slsType = "forced";
            } elseif (!$currentSecurity->is_active) {
                $slsType = "expired";
            } elseif(!$disable) {
                $slsType = 'replace';
                $this->setTitle("Replace");
            } else {
                $slsType = 'disable';
                $this->setTitle("Disable");
            }
        }

        // Now let's get the requirements
        if ($currentSecurity OR $currentSecurity != NULL) {
            $securityType = $currentSecurity->security;
        }
        if (!$currentSecurity OR $currentSecurity == NULL OR ! $securityType) {
            $securityType = SecurityType::getDefault();
        }

        $requirements = array();
        if ($securityType->length > 0) {
            $requirements[] = "A minimum of " . $securityType->length . " characters.";
        }
        if ($securityType->alpha > 0) {
            $requirements[] = $securityType->alpha . " alphabetical characters.";
        }
        if ($securityType->numeric > 0) {
            $requirements[] = $securityType->numeric . " numeric characters.";
        }
        if ($securityType->symbols > 0) {
            $requirements[] = $securityType->symbols . " symbolic characters.";
        }

        return $this->viewMake("mship.security.replace")->with("sls_type", $slsType)->with("requirements", $requirements)->with("disable", $disable);
    }

    public function postReplace($disable = false) {
        $currentSecurity = Auth::user()->get()->current_security;

        if ($disable && $currentSecurity && !$currentSecurity->security->optional) {
            return Redirect::route("mship.manage.dashboard")->with("error", "You cannot disable your secondary password.");
        }

        if ($currentSecurity && strlen($currentSecurity->value) > 1) {
            if (!Auth::user()->get()->current_security->verifyPassword(Input::get("old_password"))) {
                return Redirect::route("mship.security.replace", [(int)$disable])->with("error", "Your old password is incorrect.  Please try again.");
            }

            if ($disable) {
                $currentSecurity->delete();
                return Redirect::route("mship.manage.dashboard")->with("success", "Your secondary password has been deleted successfully.");
            }

            if (Input::get("old_password") == Input::get("new_password")) {
                return Redirect::route("mship.security.replace")->with("error", "Your new password cannot be the same as your old password.");
            }
        }

        // Check passwords match.
        if (Input::get("new_password") != Input::get("new_password2")) {
            return Redirect::route("mship.security.replace")->with("error", "The two passwords you enter did not match - you must enter your desired password, twice.");
        }
        $newPassword = Input::get("new_password");

        // Does the password meet the requirements?
        if ($currentSecurity OR $currentSecurity != NULL) {
            $securityType = SecurityType::find($currentSecurity->security_id);
        }
        if (!$currentSecurity OR $currentSecurity == NULL OR !$securityType) {
            $securityType = SecurityType::getDefault();
        }

        // Check the minimum length first.
        if (strlen($newPassword) < $securityType->length) {
            return Redirect::route("mship.security.replace")->with("error", "Your password does not meet the requirements [Length > " . $securityType->length . "]");
        }

        // Check the number of alphabetical characters.
        if (preg_match_all("/[a-zA-Z]/", $newPassword) < $securityType->alpha) {
            return Redirect::route("mship.security.replace")->with("error", "Your password does not meet the requirements [Alpha > " . $securityType->alpha . "]");
        }

        // Check the number of numeric characters.
        if (preg_match_all("/[0-9]/", $newPassword) < $securityType->numeric) {
            return Redirect::route("mship.security.replace")->with("error", "Your password does not meet the requirements [Numeric > " . $securityType->numeric . "]");
        }

        // Check the number of symbols characters.
        if (preg_match_all("/[^a-zA-Z0-9]/", $newPassword) < $securityType->symbols) {
            return Redirect::route("mship.security.replace")->with("error", "Your password does not meet the requirements [Symbols > " . $securityType->symbols . "]");
        }

        // All requirements met, set the password!
        Auth::user()->get()->setPassword($newPassword, $securityType);

        $user = Auth::user()->get();
        $user->auth_extra = 1;
        $user->auth_extra_at = \Carbon\Carbon::now();
        $user->save();
        return Redirect::route("mship.security.auth");
    }

    public function getForgotten() {
        if (!Auth::user()->get()->current_security) {
            return Redirect::route("mship.manage.dashboard");
        }

        Auth::user()->get()->resetPassword();
        Auth::user()->logout();

        return $this->viewMake("mship.security.forgotten")->with("success", "As you have forgotten your password,
                an authorisation link has been emailed to you.  Once you click this link to confirm this request
                a new password will be generated and emailed to you.<br />
                You can now close this window.");
    }

    public function getForgottenLink($code=null) {
        // Search tokens for this code!
        $token = SystemToken::where("code", "=", $code)->valid()->first();

        // Is it valid? Has it expired? Etc?
        if(!$token){
            return $this->viewMake("mship.security.forgotten")->with("error", "1You have provided an invalid password reset token.");
        }

        // Is it valid? Has it expired? Etc?
        if($token->is_used){
            return $this->viewMake("mship.security.forgotten")->with("error", "2You have provided an invalid password reset token.");
        }

        // Is it valid? Has it expired? Etc?
        if($token->is_expired){
            return $this->viewMake("mship.security.forgotten")->with("error", "3You have provided an invalid password reset token.");
        }

        // Is it valid? Has it expired? Etc?
        if(!$token->related){
            return $this->viewMake("mship.security.forgotten")->with("error", "4You have provided an invalid password reset token.");
        }

        // Let's now consume this token.
        $token->consume();

        // Generate a new password for them and then email it across!
        $password = \Models\Mship\Account\Security::generate(false);
        $passwordType = $token->related->current_security ? $token->related->current_security : \Models\Mship\Security::getDefault();
        $token->related->setPassword($password, $passwordType, TRUE);

        // Now generate an email.
        \Models\Sys\Postmaster\Queue::queue("MSHIP_SECURITY_RESET", $token->related, VATUK_ACCOUNT_SYSTEM, ["ip" => array_get($_SERVER, "REMOTE_ADDR", "Unknown"), "password" => $password]);

        Auth::user()->logout();
        return $this->viewMake("mship.security.forgotten")->with("success", "A new password has been generated
            for you and emailed to your <strong>primary</strong> VATSIM email.<br />
                You can now close this window.");
    }

}
