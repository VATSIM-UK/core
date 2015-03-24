<?php

namespace Controllers\Teamspeak;

use Redirect;
use Response;
use Models\Mship\Account;
use Models\Teamspeak\Registration as RegistrationModel;
use Models\Teamspeak\Confirmation as ConfirmationModel;
use Controllers\Teamspeak\TeamspeakAdapter;

class Registration extends \Controllers\BaseController {

    // create new registration process
    public function getNew() {
        if (count($this->_account->teamspeak_registrations) >= 3)
            return Redirect::route("mship.manage.dashboard");

        if (!$this->_account->new_registration)
            $_registration = $this->createRegistration($this->_account->account_id,
                                                                    $this->_account->last_login_ip);
        else
            $_registration = $this->_account->new_registration->load('confirmation');

        if (!$_registration->confirmation)
            $_confirmation = $this->createConfirmation($_registration->id,
                md5($_registration->created_at->timestamp), $this->_account->account_id);
        else
            $_confirmation = $_registration->confirmation;

        $this->_pageTitle = "New Registration";
        $view = $this->viewMake("teamspeak.new");
        $view->_registration = $_registration;
        $view->_confirmation = $_confirmation;
        return $view;
    }

    public function getConfirmed() {
        return $this->viewMake("teamspeak.success");
    }

    // delete registration (if owned)
    public function getDelete($registration) {
        if ($this->_account->account_id == $registration->account_id) $registration->delete();
        return Redirect::back();
    }

    // get status of registration
    public function postStatus($registration) {
        if ($this->_account->account_id == $registration->account_id)
            return Response::make($registration->status);
        else return Response::make("Cannot retrieve registration status.");
    }

    // create a new registration model
    protected function createRegistration($accountID, $registrationIP) {
        $_registration = new RegistrationModel();
        $_registration->account_id = $accountID;
        $_registration->registration_ip = $registrationIP;
        $_registration->status = "new";
        $_registration->save();
        return $_registration;
    }

    // create a new confirmation model
    protected function createConfirmation($registrationID, $confirmationString, $accountID) {
        $key_description = "CID:" . $accountID . " RegID:" . $registrationID;
        $key_custominfo = "ident=registration_id value=" . $registrationID;
        $_confirmation = new ConfirmationModel();
        $_confirmation->registration_id = $registrationID;
        $_confirmation->privilege_key = TeamspeakAdapter::run()
                                      ->serverGroupGetByName('New')
                                      ->privilegeKeyCreate($key_description, $key_custominfo);
        $_confirmation->save();
        return $_confirmation;
    }
}
