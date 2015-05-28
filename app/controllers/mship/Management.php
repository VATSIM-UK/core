<?php

namespace Controllers\Mship;

use \Redirect;
use \Input;
use \Auth;
use \Session;
use \View;
use \Models\Mship\Account;
use \Models\Sys\Token as SystemToken;

class Management extends \Controllers\BaseController {
    public function getLanding(){
        if(Auth::user()->check()){
            return Redirect::route("mship.auth.redirect");
        }

        return $this->viewMake("mship.management.landing");
    }
    public function getDashboard(){
        // Load necessary data, early!
        $this->_account->load(
            "emails",
            "qualifications", "qualifications.qualification",
            "states", "teamspeakRegistrations"
        );

        return $this->viewMake("mship.management.dashboard");
    }

    public function getEmailAdd(){
        return $this->viewMake("mship.management.email.add");
    }

    public function postEmailAdd(){
        $email = strtolower(Input::get("new_email"));
        $email2 = strtolower(Input::get("new_email2"));

        // Check they match!
        if(strcasecmp($email, $email2) != 0){
            return Redirect::route("mship.manage.email.add")->withError("Emails entered are different.  You need to enter the same email, twice.");
        }

        // Let's just try and make it, now!
        $newEmail = $this->_account->addEmail($email);

        if(!$newEmail){
            return Redirect::route("mship.manage.email.add")->withError("This email cannot be added, as it has already been used.");
        }

        return Redirect::route("mship.manage.dashboard")->withSuccess("Your new email (".$email." has been added successfully! You will be sent a verification link to activate this email address.");
    }

    public function getVerifyEmail($code){
        // Search tokens for this code!
        $token = SystemToken::where("code", "=", $code)->valid()->first();

        // Is it valid? Has it expired? Etc?
        if(!$token){
            return $this->viewMake("mship.management.email.verify")->with("error", "You have provided an invalid email verification token. (ERR1)");
        }

        // Is it valid? Has it expired? Etc?
        if($token->is_used){
            return $this->viewMake("mship.management.email.verify")->with("error", "You have provided an invalid email verification token. (ERR2)");
        }

        // Is it valid? Has it expired? Etc?
        if($token->is_expired){
            return $this->viewMake("mship.management.email.verify")->with("error", "You have provided an invalid email verification token. (ERR3)");
        }

        // Is it valid and linked to something?!?!
        if(!$token->related OR $token->type != "mship_account_email_verify"){
            return $this->viewMake("mship.management.email.verify")->with("error", "You have provided an invalid email verification token. (ERR4)");
        }

        // Let's now consume this token.
        $token->consume();

        // Mark the email as verified!
        $token->related->verified_at = \Carbon\Carbon::now();
        $token->related->save();

        // Consumed, let's send away!
        return Redirect::route("mship.manage.dashboard")->withSuccess("Your new email address (".$token->related->email.") has been verified!");
    }
}
