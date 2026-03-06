<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransfer\ApplicationFacilitySelectedRequested;
use App\Http\Requests\VisitTransfer\ApplicationManualFacilityRequest;
use App\Http\Requests\VisitTransfer\ApplicationRefereeAddRequest;
use App\Http\Requests\VisitTransfer\ApplicationRefereeDeleteRequest;
use App\Http\Requests\VisitTransfer\ApplicationStartRequest;
use App\Http\Requests\VisitTransfer\ApplicationStatementSubmitRequest;
use App\Http\Requests\VisitTransfer\ApplicationSubmitRequest;
use App\Http\Requests\VisitTransfer\ApplicationWithdrawRequest;
use App\Models\VisitTransfer\Reference;
use App\Services\VisitTransfer\ApplicationFlowService;
use App\Services\VisitTransfer\DTO\ApplicationActionResult;
use Auth;
use Illuminate\Support\Facades\Request;
use Redirect;

class Application extends BaseController
{
    public function __construct(private ApplicationFlowService $applicationFlowService)
    {
        parent::__construct();
    }

    public function getStart($applicationType, $trainingTeam = 'atc')
    {
        $this->authorize('create', new \App\Models\VisitTransfer\Application);

        if ($this->applicationFlowService->shouldAutoStartApplication((string) $trainingTeam)) {
            $result = $this->applicationFlowService->startApplicationAction(
                Auth::user(),
                (string) $applicationType,
                (string) $trainingTeam,
                'visiting.landing'
            );

            return Redirect::route($result->route, $result->routeParameters)->with((string) $result->level, (string) $result->message);
        }

        $this->setTitle('Start Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.terms')
            ->with('applicationType', $applicationType)
            ->with('trainingTeam', $trainingTeam)
            ->with('application', new \App\Models\VisitTransfer\Application);
    }

    public function postStart(ApplicationStartRequest $request)
    {
        $result = $this->applicationFlowService->startApplicationAction(
            Auth::user(),
            (string) Request::input('application_type'),
            (string) Request::input('training_team'),
            'visiting.application.start',
            [Request::input('application_type')]
        );

        return Redirect::route($result->route, $result->routeParameters)->with((string) $result->level, (string) $result->message);
    }

    public function getContinue(\App\Models\VisitTransfer\Application $application)
    {
        $redirectData = $this->applicationFlowService->getContinueRedirectData($application);

        return Redirect::route($redirectData->route, $redirectData->routeParameters);
    }

    public function getFacility()
    {
        $this->authorize('select-facility', $this->getCurrentOpenApplicationForUser());

        $this->setTitle('Facility - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.facility')
            ->with('application', $this->getCurrentOpenApplicationForUser())
            ->with('facilities', $this->getCurrentOpenApplicationForUser()->potential_facilities);
    }

    public function postManualFacility(ApplicationManualFacilityRequest $request, \App\Models\VisitTransfer\Application $application)
    {
        return $this->redirectFromActionResult(
            $this->applicationFlowService->setManualFacilityAction($application, (string) Request::input('facility-code'))
        );
    }

    public function postFacility(ApplicationFacilitySelectedRequested $request, \App\Models\VisitTransfer\Application $application)
    {
        return $this->redirectFromActionResult(
            $this->applicationFlowService->setFacilityAction($application, (int) Request::input('facility_id'))
        );
    }

    public function getStatement(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('add-statement', $application);

        $application->load('facility');

        $this->setTitle('Statement - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.statement')
            ->with('application', $application);
    }

    public function postStatement(ApplicationStatementSubmitRequest $request, \App\Models\VisitTransfer\Application $application)
    {
        return $this->redirectFromActionResult(
            $this->applicationFlowService->setStatementAction($application, (string) Request::input('statement'))
        );
    }

    public function getReferees(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('add-referee', $application);

        $application->load('referees.account');

        $this->setTitle('Referees - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.referees')
            ->with('application', $application);
    }

    public function postReferees(ApplicationRefereeAddRequest $request, \App\Models\VisitTransfer\Application $application)
    {
        $result = $this->applicationFlowService->addRefereeAction(
            $application,
            Auth::user(),
            (string) Request::input('referee_cid'),
            Request::input('referee_email'),
            Request::input('referee_relationship')
        );

        if ($result->useBackRedirect) {
            $redirect = Redirect::back()->with((string) $result->level, (string) $result->message);

            if ($result->withInput) {
                return $redirect->withInput();
            }

            return $redirect;
        }

        return Redirect::route($result->route, $result->routeParameters)->with((string) $result->level, (string) $result->message);
    }

    public function postRefereeDelete(ApplicationRefereeDeleteRequest $request, \App\Models\VisitTransfer\Application $application, Reference $reference)
    {
        $this->applicationFlowService->deleteReferee($reference);

        return Redirect::route('visiting.application.referees', [$application->public_id])->withSuccess('Referee '.$reference->account->name.' deleted.');
    }

    public function getSubmit(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('submit-application', $application);

        $this->setTitle('Submit - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.submission')
            ->with('application', $application);
    }

    public function postSubmit(ApplicationSubmitRequest $request, \App\Models\VisitTransfer\Application $application)
    {
        return $this->redirectFromActionResult($this->applicationFlowService->submitAction($application));
    }

    public function getWithdraw(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('withdraw-application', $application);

        $this->setTitle('Withdraw - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.withdraw')
            ->with('application', $application);
    }

    public function postWithdraw(ApplicationWithdrawRequest $request, \App\Models\VisitTransfer\Application $application)
    {
        return $this->redirectFromActionResult($this->applicationFlowService->withdrawAction($application));
    }

    public function getView(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('view', $application);

        $application->load('facility')->load('referees.account');

        $this->setTitle('View Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.view')
            ->with('application', $application);
    }

    private function redirectFromActionResult(ApplicationActionResult $result)
    {
        if ($result->useBackRedirect) {
            $redirect = Redirect::back()->with((string) $result->level, (string) $result->message);

            if ($result->withInput) {
                return $redirect->withInput();
            }

            return $redirect;
        }

        return Redirect::route($result->route, $result->routeParameters)->with((string) $result->level, (string) $result->message);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return $this->applicationFlowService->getCurrentOpenApplicationForUser(Auth::user());
    }
}
