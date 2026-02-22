<?php

namespace App\Http\Controllers\Mship;

use App\Models\Mship\Account\Email as AccountEmail;
use App\Services\Mship\ManagementFlowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Redirect;

class Management extends \App\Http\Controllers\BaseController
{
    public function __construct(private ManagementFlowService $managementFlowService)
    {
        parent::__construct();
    }

    public function getLanding()
    {
        if (Auth::check()) {
            return redirect()->intended(route('mship.manage.dashboard'));
        }

        return $this->viewMake('mship.management.landing');
    }

    public function getDashboard()
    {
        $this->account->load(
            'secondaryEmails',
            'qualifications',
            'states',
            'teamspeakRegistrations'
        );

        $data = $this->managementFlowService->getDashboardData($this->account);

        return $this->viewMake('mship.management.dashboard')
            ->with('pluginKeys', $data['pluginKeys'])
            ->with('roster', $data['roster']);
    }

    public function getEmailAdd()
    {
        return $this->viewMake('mship.management.email.add');
    }

    public function postEmailAdd()
    {
        $result = $this->managementFlowService->addSecondaryEmail(
            $this->account,
            (string) Request::input('new_email'),
            (string) Request::input('new_email2')
        );

        if (! $result['ok']) {
            return Redirect::route((string) $result['route'])
                ->withError((string) $result['message']);
        }

        return Redirect::route('mship.manage.dashboard')
            ->withSuccess((string) $result['message']);
    }

    public function getEmailDelete(AccountEmail $email)
    {
        if ($email->account->id !== $this->account->id) {
            return Redirect::route('mship.manage.dashboard');
        }

        return $this->viewMake('mship.management.email.delete')
            ->with('email', $email)
            ->with('assignments', $email->ssoEmails);
    }

    public function postEmailDelete(AccountEmail $email)
    {
        if (! $this->managementFlowService->deleteSecondaryEmail($this->account, $email)) {
            return Redirect::route('mship.manage.dashboard');
        }

        return Redirect::route('mship.manage.dashboard')
            ->withSuccess('Your secondary email ('.$email->email.') has been removed!');
    }

    public function getEmailAssignments()
    {
        $data = $this->managementFlowService->getEmailAssignmentsData($this->account);

        return $this->viewMake('mship.management.email.assignments')
            ->with('userPrimaryEmail', $data['userPrimaryEmail'])
            ->with('userSecondaryVerified', $data['userSecondaryVerified'])
            ->with('userMatrix', $data['userMatrix']);
    }

    public function postEmailAssignments()
    {
        $this->managementFlowService->updateEmailAssignments($this->account, Request::all());

        return Redirect::route('mship.manage.dashboard')->withSuccess('Email assignments updated successfully! These will take effect the next time you login to the system.');
    }

    public function getVerifyEmail($code)
    {
        $result = $this->managementFlowService->verifyEmailToken($code);

        if (! $result['ok']) {
            return $this->viewMake('mship.management.email.verify')->with('error', $result['message']);
        }

        if ($this->account) {
            return Redirect::route('mship.manage.dashboard')->withSuccess($result['message']);
        }

        return $this->viewMake('mship.management.email.verify')->with('success', $result['message']);
    }

    public function requestCertCheck()
    {
        if (! $this->managementFlowService->requestCertCheck((int) Auth::user()->id)) {
            return redirect()->route('mship.manage.dashboard')->withError('You requested an update with the central VATSIM database recently. Try again later.');
        }

        return redirect()->route('mship.manage.dashboard')->withSuccess('Account update requested. This may take up to 5 minutes to complete.');
    }
}
