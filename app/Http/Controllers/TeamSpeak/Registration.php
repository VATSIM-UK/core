<?php

namespace App\Http\Controllers\TeamSpeak;

use App\Models\TeamSpeak\Registration as RegistrationModel;
use App\Services\TeamSpeak\RegistrationFlowService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class Registration extends \App\Http\Controllers\BaseController
{
    public function __construct(private RegistrationFlowService $registrationFlowService)
    {
        parent::__construct();
    }

    public function getNew()
    {
        if (! $this->registrationFlowService->canStartRegistration($this->account)) {
            return Redirect::route('mship.manage.dashboard');
        }

        $registration = $this->registrationFlowService->getOrCreateRegistration($this->account, (string) Request::ip());
        $confirmation = $this->registrationFlowService->getOrCreateConfirmation($registration, (int) $this->account->id);

        $this->pageTitle = 'New Registration';

        return $this->viewMake('teamspeak.new')
            ->withRegistration($registration)
            ->withConfirmation($confirmation)
            ->with('teamspeak_url', config('teamspeak.host'))
            ->with('auto_url', $this->registrationFlowService->generateAutoUrl($this->account, $confirmation));
    }

    public function getConfirmed()
    {
        return $this->viewMake('teamspeak.success');
    }

    public function getDelete(RegistrationModel $mshipRegistration)
    {
        $this->registrationFlowService->deleteRegistration($this->account, $mshipRegistration);

        return Redirect::back();
    }

    public function postStatus(RegistrationModel $mshipRegistration)
    {
        return Response::make($this->registrationFlowService->getRegistrationStatusResponseBody($this->account, $mshipRegistration));
    }
}
