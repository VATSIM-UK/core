<?php

namespace App\Http\Controllers\TeamSpeak;

use App\Models\TeamSpeak\Confirmation as ConfirmationModel;
use App\Models\TeamSpeak\Registration as RegistrationModel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class Registration extends \App\Http\Controllers\BaseController
{
    // create new registration process
    public function getNew()
    {
        if (count($this->account->teamspeakRegistrations) > 25) {
            return Redirect::route('mship.manage.dashboard');
        }

        if (! $this->account->new_ts_registration) {
            $registration_ip = Request::ip();
            $registration = $this->createRegistration($this->account->id, $registration_ip);
        } else {
            $registration = $this->account->new_ts_registration->load('confirmation');
        }

        if (! $registration->confirmation) {
            $confirmation = $this->createConfirmation(
                $registration->id,
                md5($registration->created_at->timestamp),
                $this->account->id
            );
        } else {
            $confirmation = $registration->confirmation;
        }

        $base = sprintf('%s%s%s', 'ts3server://', config('services.teamspeak.host'), '?');
        $query = http_build_query([
            'nickname' => sprintf('%s %s', $this->account->name, $this->account->id),
            'token' => $confirmation->privilege_key,
        ], encoding_type: PHP_QUERY_RFC3986);

        $this->pageTitle = 'New Registration';
        $view = $this->viewMake('teamspeak.new')
            ->withRegistration($registration)
            ->withConfirmation($confirmation)
            ->with('teamspeak_url', config('teamspeak.host'))
            ->with('auto_url', sprintf('%s%s', $base, $query));

        return $view;
    }

    public function getConfirmed()
    {
        return $this->viewMake('teamspeak.success');
    }

    // delete registration (if owned)
    public function getDelete(RegistrationModel $mshipRegistration)
    {
        if ($this->account->id == $mshipRegistration->account_id) {
            $mshipRegistration->delete();
        }

        return Redirect::back();
    }

    // get status of registration
    public function postStatus(RegistrationModel $mshipRegistration)
    {
        if ($this->account->id == $mshipRegistration->account_id) {
            return ($mshipRegistration->dbid === null) ? Response::make('new') : Response::make('active');
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
