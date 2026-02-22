<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Exceptions\Mship\InvalidCIDException;
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
use Auth;
use Exception;
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
            try {
                $application = $this->applicationFlowService->startApplication(Auth::user(), $applicationType, $trainingTeam);
            } catch (Exception $e) {
                return Redirect::route('visiting.landing')->withError($e->getMessage());
            }

            return Redirect::route('visiting.application.facility', [$application->public_id])->withSuccess('Application started! Please complete all sections to submit your application.');
        }

        $this->setTitle('Start Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.terms')
            ->with('applicationType', $applicationType)
            ->with('trainingTeam', $trainingTeam)
            ->with('application', new \App\Models\VisitTransfer\Application);
    }

    public function postStart(ApplicationStartRequest $request)
    {
        try {
            $application = $this->applicationFlowService->startApplication(Auth::user(), (string) Request::input('application_type'), (string) Request::input('training_team'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.start', [Request::input('application_type')])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.facility', [$application->public_id])->withSuccess('Application started! Please complete all sections to submit your application.');
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
        try {
            $this->applicationFlowService->setManualFacility($application, (string) Request::input('facility-code'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.facility', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Facility selection saved!');
    }

    public function postFacility(ApplicationFacilitySelectedRequested $request, \App\Models\VisitTransfer\Application $application)
    {
        try {
            $this->applicationFlowService->setFacilityById($application, (int) Request::input('facility_id'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.facility', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Facility selection saved!');
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
        try {
            $this->applicationFlowService->setStatement($application, (string) Request::input('statement'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.statement', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Statement completed');
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
        try {
            $flowResult = $this->applicationFlowService->addReferee(
                $application,
                Auth::user(),
                (string) Request::input('referee_cid'),
                Request::input('referee_email'),
                Request::input('referee_relationship')
            );
        } catch (InvalidCIDException $e) {
            return Redirect::back()
                ->withError("There doesn't seem to be a VATSIM user with that ID.")
                ->withInput();
        } catch (Exception $e) {
            $error = $this->applicationFlowService->mapRefereeAddException($e);

            if ($error->useBackRedirect) {
                return Redirect::back()
                    ->withError($error->message)
                    ->withInput();
            }

            return Redirect::route('visiting.application.referees', [$application->public_id])->withError($error->message);
        }

        return Redirect::route($flowResult['redirectRoute'], [$application->public_id])->withSuccess('Referee '.Request::input('referee_cid').' added successfully! They will not be contacted until you submit your application.');
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
        try {
            $this->applicationFlowService->submit($application);
        } catch (Exception $e) {
            return Redirect::route('visiting.application.submit', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.view', [$application->public_id])->withSuccess('Your application has been submitted! You will be notified when staff have reviewed the details.');
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
        try {
            $this->applicationFlowService->withdraw($application);
        } catch (Exception $e) {
            return Redirect::route('visiting.application.withdraw', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('Your application has been withdrawn! You can submit a new application as required.');
    }

    public function getView(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('view', $application);

        $application->load('facility')->load('referees.account');

        $this->setTitle('View Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.view')
            ->with('application', $application);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return $this->applicationFlowService->getCurrentOpenApplicationForUser(Auth::user());
    }
}
