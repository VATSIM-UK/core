<?php namespace App\Http\Controllers\TeamSpeak;

use Redirect;
use Response;
use App\Models\Mship\Account;
use App\Models\TeamSpeak\Registration as RegistrationModel;
use App\Models\TeamSpeak\Confirmation as ConfirmationModel;
use App\Libraries\TeamSpeak;

class Registration extends \App\Http\Controllers\BaseController
{

    // create new registration process
    public function getNew()
    {
        if (count($this->_account->teamspeak_registrations) >= 3) {
            return Redirect::route("mship.manage.dashboard");
        }

        if (!$this->_account->new_ts_registration) {
            $registration_ip = $_SERVER['REMOTE_ADDR'];
            $registration = $this->createRegistration($this->_account->id, $registration_ip);
        } else {
            $registration = $this->_account->new_ts_registration->load('confirmation');
        }

        if (!$registration->confirmation) {
            $confirmation = $this->createConfirmation(
                $registration->id,
                md5($registration->created_at->timestamp),
                $this->_account->id
            );
        } else {
            $confirmation = $registration->confirmation;
        }

        $autoURL = "ts3server://" . $_ENV['TS_HOST'] . "?nickname=" . $this->_account->name_first . "%20";
        $autoURL.= $this->_account->name_last . "&amp;token=" . $confirmation->privilege_key;

        $this->_pageTitle = "New Registration";
        $view = $this->viewMake("teamspeak.new")
                     ->withRegistration($registration)
                     ->withConfirmation($confirmation)
                     ->withAutoUrl($autoURL);
        return $view;
    }

    public function getConfirmed()
    {
        return $this->viewMake("teamspeak.success");
    }

    // delete registration (if owned)
    public function getDelete($registration)
    {
        if ($this->_account->id == $registration->account_id) {
            $registration->delete();
        }
        return Redirect::back();
    }

    // get status of registration
    public function postStatus($registration)
    {
        if ($this->_account->id == $registration->account_id) {
            return ($registration->dbid === null) ? Response::make('new') : Response::make('active');
        } else {
            return Response::make("Cannot retrieve registration status.");
        }
    }

    // create a new registration model
    protected function createRegistration($accountID, $registrationIP)
    {
        \Log::info($accountID);
        \Log::info(\Auth::user());
        $_registration = new RegistrationModel();
        $_registration->account_id = $accountID;
        $_registration->registration_ip = $registrationIP;
        $_registration->save();
        return $_registration;
    }

    // create a new confirmation model
    protected function createConfirmation($registrationID, $confirmationString, $accountID)
    {
        $key_description = "CID:" . $accountID . " RegID:" . $registrationID;
        $key_custominfo = "ident=registration_id value=" . $registrationID;
        $_confirmation = new ConfirmationModel();
        $_confirmation->registration_id = $registrationID;
        $_confirmation->privilege_key = \App\Libraries\TeamSpeak::run()
                                      ->serverGroupGetByName('New')
                                      ->privilegeKeyCreate($key_description, $key_custominfo);
        $_confirmation->save();
        return $_confirmation;
    }
}
