<?php

namespace Controllers\Mship;

use \Redirect;
use \Session;
use \Input;
use \View;
use \Models\Mship\Account\Account;
use \Models\Mship\Account\Security as AccountSecurity;
use \Models\Mship\Security as SecurityType;
use \Models\Sys\Token as SystemToken;

class Security extends \Controllers\BaseController {

    public function get_auth() {
        // Let's check whether we even NEED this.
        if (Session::get("auth_extra", false) OR ! $this->_current_account->current_security OR $this->_current_account->current_security == NULL) {
            return Redirect::to("/mship/auth/redirect");
        }

        // Next, do we need to replace/reset?
        if (!$this->_current_account->current_security->is_active) {
            return Redirect::to("/mship/security/replace");
        }

        // So we need it.  Let's go!
        return $this->viewMake("mship.security.auth");
    }

    public function post_auth() {
        if ($this->_current_account->current_security->verifyPassword(Input::get("password"))) {
            Session::set("auth_extra", true);
            return Redirect::to("/mship/auth/redirect");
        }
        return Redirect::to("/mship/security/auth")->with("error", "Invalid password entered - please try again.");
    }

    public function get_enable() {
        return Redirect::to("/mship/security/replace");
    }

    public function get_replace($disable = false) {
        $currentSecurity = $this->_current_account->current_security;

        if ($disable && $currentSecurity && !$currentSecurity->security->optional) {
            return Redirect::to("/mship/manage/dashboard")->with("error", "You cannot disable your secondary password.");
        } elseif ($disable && !$currentSecurity) {
            $disable = false;
        } else {
            $this->_pageTitle = "Disable";
        }

        if (!$currentSecurity OR $currentSecurity == NULL) {
            $this->_pageTitle = "Create";
            $slsType = 'requested';
        } else {
            if (strlen($currentSecurity->value) < 1) {
                $this->_pageTitle = "Create";
                $slsType = "forced";
            } elseif (!$currentSecurity->is_active) {
                $slsType = "expired";
            } else {
                $slsType = 'replace';
            }
        }

        // Now let's get the requirements
        if ($currentSecurity OR $currentSecurity != NULL) {
            $securityType = SecurityType::find($currentSecurity->type);
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

    public function post_replace($disable = false) {
        $currentSecurity = $this->_current_account->current_security;

        if ($disable && $currentSecurity && !$currentSecurity->security->optional) {
            return Redirect::to("/mship/manage/dashboard")->with("error", "You cannot disable your secondary password.");
        }

        if ($currentSecurity && strlen($currentSecurity->value) > 1) {
            if (!$this->_current_account->current_security->verifyPassword(Input::get("old_password"))) {
                return Redirect::to("/mship/security/replace".($disable ? "/1" : ""))->with("error", "Your old password is incorrect.  Please try again.");
            }

            if ($disable) {
                $currentSecurity->delete();
                return Redirect::to("/mship/manage/dashboard")->with("success", "Your secondary password has been deleted successfully.");
            }

            if (Input::get("old_password") == Input::get("new_password")) {
                return Redirect::to("/mship/security/replace")->with("error", "Your new password cannot be the same as your old password.");
            }
        }

        // Check passwords match.
        if (Input::get("new_password") != Input::get("new_password2")) {
            return Redirect::to("/mship/security/replace")->with("error", "The two passwords you enter do not match - you must enter your old password twice.");
        }
        $newPassword = Input::get("new_password");

        // Does the password meet the requirements?
        if ($currentSecurity OR $currentSecurity != NULL) {
            $securityType = SecurityType::find($currentSecurity->type);
        }
        if (!$currentSecurity OR $currentSecurity == NULL OR ! $securityType) {
            $securityType = SecurityType::getDefault();
        }

        // Check the minimum length first.
        if (strlen($newPassword) < $securityType->length) {
            return Redirect::to("/mship/security/replace")->with("error", "Your password does not meet the requirements [Length > " . $securityType->length . "]");
        }

        // Check the number of alphabetical characters.
        if (preg_match_all("/[a-zA-Z]/", $newPassword) < $securityType->alpha) {
            return Redirect::to("/mship/security/replace")->with("error", "Your password does not meet the requirements [Alpha > " . $securityType->alpha . "]");
        }

        // Check the number of numeric characters.
        if (preg_match_all("/[0-9]/", $newPassword) < $securityType->numeric) {
            return Redirect::to("/mship/security/replace")->with("error", "Your password does not meet the requirements [Numeric > " . $securityType->numeric . "]");
        }

        // Check the number of symbols characters.
        if (preg_match_all("/[^a-zA-Z0-9]/", $newPassword) < $securityType->symbols) {
            return Redirect::to("/mship/security/replace")->with("error", "Your password does not meet the requirements [Symbols > " . $securityType->symbols . "]");
        }

        // All requirements met, set the password!
        $this->_current_account->setPassword($newPassword, $securityType);

        Session::set("auth_extra", true);
        return Redirect::to("/mship/security/auth");
    }

    public function get_forgotten() {
        if (!$this->_current_account->current_security) {
            return Redirect::to("/mship/manage/dashboard");
        }

        // Now generate a new token for the email.
        $token = SystemToken::generate("mship_account_security_reset", false, $this->_current_account);

        // Let's send them an email with this information!
        \Models\Sys\Postmaster\Queue::queue("MSHIP_SECURITY_FORGOTTEN", $this->_current_account, VATUK_ACCOUNT_SYSTEM, ["ip" => array_get($_SERVER, "REMOTE_ADDR", "Unknown"), "token" => $token]);

        Session::flush();
        return $this->viewMake("mship.security.forgotten")->with("success", "As you have forgotten your password,
                an authorisation link has been emailed to you.  Once you click this link to confirm this request
                a new password will be generated and emailed to you.<br />
                You can now close this window.");
    }

    public function get_forgotten_link($code=null) {
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
        $token->related->setPassword($password, $passwordType);

        // We need to modify the expiry!
        $token->related->current_security->expires_at = \Carbon\Carbon::now()->toDateTimeString();
        $token->related->current_security->save();

        // Now generate an email.
        \Models\Sys\Postmaster\Queue::queue("MSHIP_SECURITY_RESET", $this->_current_account, VATUK_ACCOUNT_SYSTEM, ["ip" => array_get($_SERVER, "REMOTE_ADDR", "Unknown"), "password" => $password]);

        Session::flush();
        return $this->viewMake("mship.security.forgotten")->with("success", "A new password has been generated
            for you and emailed to your <strong>primary</strong> VATSIM email.<br />
                You can now close this window.");
    }

}
