<?php

namespace App\Http\Controllers\TeamSpeak;

use Redirect;
use Response;
use App\Libraries\TeamSpeak;
use App\Models\TeamSpeak\Confirmation as ConfirmationModel;
use App\Models\TeamSpeak\Registration as RegistrationModel;

class Registration extends \App\Http\Controllers\BaseController
{
    // create new registration process
    public function getNew()
    {
        if (count($this->account->teamspeak_registrations) >= 3) {
            return Redirect::route('mship.manage.dashboard');
        }

        if (!$this->account->new_ts_registration) {
            $registration_ip = $_SERVER['REMOTE_ADDR'];
            $registration = $this->createRegistration($this->account->id, $registration_ip);
        } else {
            $registration = $this->account->new_ts_registration->load('confirmation');
        }

        if (!$registration->confirmation) {
            $confirmation = $this->createConfirmation(
                $registration->id,
                md5($registration->created_at->timestamp),
                $this->account->id
            );
        } else {
            $confirmation = $registration->confirmation;
        }

        $autoURL = 'ts3server://'.$_ENV['TS_HOST'].'?nickname='.$this->account->name_first.'%20';
        $autoURL .= $this->account->name_last.'&amp;token='.$confirmation->privilege_key;

        $this->pageTitle = 'New Registration';
        $view = $this->viewMake('teamspeak.new')
                     ->withRegistration($registration)
                     ->withConfirmation($confirmation)
                     ->withAutoUrl($autoURL);

        return $view;
    }

    public function getConfirmed()
    {
        return $this->viewMake('teamspeak.success');
    }

    // delete registration (if owned)
    public function getDelete($registration)
    {
        if ($this->account->id == $registration->account_id) {
            $registration->delete();
        }

        return Redirect::back();
    }

    // get status of registration
    public function postStatus($registration)
    {
        if ($this->account->id == $registration->account_id) {
            return ($registration->dbid === null) ? Response::make('new') : Response::make('active');
        } else {
            return Response::make('Cannot retrieve registration status.');
        }
    }

    // create a new registration model
    protected function createRegistration($accountID, $registrationIP)
    {
        $_registration = new RegistrationModel();
        $_registration->account_id = $accountID;
        $_registration->registration_ip = $registrationIP;
        $_registration->save();

        return $_registration;
    }

    // create a new confirmation model
    protected function createConfirmation($registrationID, $confirmationString, $accountID)
    {
        $key_description = 'CID:'.$accountID.' RegID:'.$registrationID;
        $key_custominfo = 'ident=registration_id value='.$registrationID;
        $_confirmation = new ConfirmationModel();
        $_confirmation->registration_id = $registrationID;
        $_confirmation->privilege_key = \App\Libraries\TeamSpeak::run()
                                      ->serverGroupGetByName('New')
                                      ->privilegeKeyCreate($key_description, $key_custominfo);
        $_confirmation->save();

        return $_confirmation;
    }
}
