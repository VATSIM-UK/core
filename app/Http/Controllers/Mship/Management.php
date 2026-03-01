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
        if ($this->managementFlowService->shouldRedirectLanding(Auth::check())) {
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
        $result = $this->managementFlowService->getAddSecondaryEmailRedirectResult(
            $this->account,
            (string) Request::input('new_email'),
            (string) Request::input('new_email2')
        );

        return Redirect::route($result->route)->with((string) $result->level, (string) $result->message);
    }

    public function getEmailDelete(AccountEmail $email)
    {
        $result = $this->managementFlowService->getEmailDeleteViewResult($this->account, $email);

        if ($result->redirect) {
            return Redirect::route($result->route);
        }

        return $this->viewMake('mship.management.email.delete')->with($result->data);
    }

    public function postEmailDelete(AccountEmail $email)
    {
        $result = $this->managementFlowService->getDeleteSecondaryEmailRedirectResult($this->account, $email);

        if (! $result->hasFlashMessage()) {
            return Redirect::route($result->route);
        }

        return Redirect::route($result->route)->with((string) $result->level, (string) $result->message);
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
        $result = $this->managementFlowService->getVerifyEmailPageResult($code, (bool) $this->account);

        if (! $result->redirect) {
            return $this->viewMake('mship.management.email.verify')->with((string) $result->level, (string) $result->message);
        }

        return Redirect::route($result->route)->withSuccess((string) $result->message);
    }

    public function requestCertCheck()
    {
        $result = $this->managementFlowService->getRequestCertCheckRedirectResult((int) $this->account->id);

        return redirect()->route($result->route)->with((string) $result->level, (string) $result->message);
    }
}
