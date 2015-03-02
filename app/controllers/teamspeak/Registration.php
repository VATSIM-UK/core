<?php

namespace Controllers\Teamspeak;

use \Redirect;
use \Auth;
use \Session;
use \View;
use \Models\Mship\Account;
use \Models\Teamspeak\Registration as RegistrationModel;

class Registration extends \Controllers\BaseController {

    public function getNew() {
		if (count($this->_account->teamspeak_registrations) >= 3) return Redirect::route("mship.manage.dashboard");
		
		if (!$this->_account->new_registration) {
			$_registration = new RegistrationModel;
			$_registration->account_id = $this->_account->account_id;
			$_registration->registration_ip = ip2long($this->_account->last_login_ip);
			$_registration->save();
		} else {
			$_registration = $this->_account->new_registration;
		}
		
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