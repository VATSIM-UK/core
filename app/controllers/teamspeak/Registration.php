<?php

namespace Controllers\Teamspeak;

use \Redirect;
use \Auth;
use \Session;
use \View;
use \Models\Mship\Account;
use \Models\Teamspeak\Registration as RegistrationModel;
use \Models\Teamspeak\Confirmation as ConfirmationModel;

class Registration extends \Controllers\BaseController {

    public function getNew() {
		if (count($this->_account->teamspeak_registrations) >= 3) return Redirect::route("mship.manage.dashboard");
		
        // find or obtain registration
        // to do - check if confirmation exists for registration
		if (!$this->_account->new_registration)
            $_registration = $this->createRegistration($this->_account->account_id, $this->_account->last_login_ip);
        else
            $_registration = $this->_account->new_registration;

        if (!$_registration->confirmation)
            $_confirmation = $this->createConfirmation($_registration->id, 'placeholder', md5($_registration->created_at->timestamp));
		else
            $_confirmation = $_registration->confirmation;

        $this->_pageTitle = "New Registration";
        $view = $this->viewMake("teamspeak.new");
		$view->_registration = $_registration;
        $view->_confirmation = $_confirmation;
		return $view;
    }

    public function postCreate($uuid) {
		if (count($this->_account->registrations) >= 3) return Redirect::route("mship.manage.dashboard");
		$_registration = new Registration($this->_account->account_id);
    }

    public function postDelete($uuid) {

    }

    public function createRegistration($accountID, $registrationIP) {
        $_registration = new RegistrationModel();
        $_registration->account_id = $accountID;
        $_registration->registration_ip = $registrationIP;
        $_registration->status = "new";
        $_registration->save();
        return $_registration;
    }

    public function createConfirmation($registrationID, $privilegeKey, $confirmationString) {
        $_confirmation = new ConfirmationModel();
        $_confirmation->registration_id = $registrationID;
        $_confirmation->privilege_key = $privilegeKey;
        $_confirmation->confirmation_string = $confirmationString;
        $_confirmation->save();
        return $_confirmation;
    }
}