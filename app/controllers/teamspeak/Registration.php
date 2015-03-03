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
		if (!$this->_account->new_registration) {
            $_registration = new RegistrationModel();
			$_registration->account_id = $this->_account->account_id;
            $_registration->registration_ip = $this->_account->last_login_ip;
            $_registration->save();

            $_confirmation = new ConfirmationModel();
            $_confirmation->registration_id = $_registration->id;
            $_confirmation->confirmation_string = md5(ip2long($_registration->registration_ip) 
                                                                                        + $_registration->created_at->timestamp);
            $_confirmation->save();
		} else {
			$_registration = $this->_account->new_registration;
		}

        // obtain the registration confirmation


		
        $this->_pageTitle = "New Registration";
        $view = $this->viewMake("teamspeak.new");
		$view->_registration = $_registration;
		return $view;
    }

    public function postCreate($uuid) {
		if (count($this->_account->registrations) >= 3) return Redirect::route("mship.manage.dashboard");
		$_registration = new Registration($this->_account->account_id);
    }

    public function postDelete($uuid) {

    }
}