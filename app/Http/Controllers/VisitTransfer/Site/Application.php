<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Exceptions\Mship\InvalidCIDException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransfer\ApplicationFacilitySelectedRequested;
use App\Http\Requests\VisitTransfer\ApplicationStartRequest;
use App\Http\Requests\VisitTransfer\ApplicationStatementSubmitRequest;
use App\Http\Requests\VisitTransfer\ApplicationSubmitRequest;
use App\Http\Requests\VisitTransfer\ApplicationWithdrawRequest;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Facility;
use Auth;
use ErrorException;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Redirect;
use Validator;

class Application extends BaseController
{
    public function getStart($applicationType, $trainingTeam = 'atc')
    {
        $this->authorize('create', new \App\Models\VisitTransfer\Application);

        if ($trainingTeam == 'pilot') {
            try {
                $application = $this->startApplication($applicationType, $trainingTeam);
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
            $application = $this->startApplication(Request::input('application_type'), Request::input('training_team'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.start', [Request::input('application_type')])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.facility', [$application->public_id])->withSuccess('Application started! Please complete all sections to submit your application.');
    }

    private function startApplication($type, $team)
    {
        return Auth::user()->createVisitingTransferApplication([
            'type' => $type,
            'training_team' => $team,
        ]);
    }

    public function getContinue(\App\Models\VisitTransfer\Application $application)
    {
        if (Gate::allows('select-facility', $application)) {
            return Redirect::route('visiting.application.facility', [$application->public_id]);
        }

        if (Gate::allows('add-statement', $application) && $application->statement == null) {
            return Redirect::route('visiting.application.statement', [$application->public_id]);
        }

        if (Gate::allows('submit-application', $application)) {
            return Redirect::route('visiting.application.submit', [$application->public_id]);
        }

        if (Gate::allows('view', $application)) {
            return Redirect::route('visiting.application.view', [$application->public_id]);
        }

        return Redirect::route('visiting.landing');
    }

    public function getFacility()
    {
        $this->authorize('select-facility', $this->getCurrentOpenApplicationForUser());

        $this->setTitle('Facility - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.facility')
            ->with('application', $this->getCurrentOpenApplicationForUser())
            ->with('facilities', $this->getCurrentOpenApplicationForUser()->potential_facilities);
    }

    public function postManualFacility(Request $request, \App\Models\VisitTransfer\Application $application)
    {
        $validator = Validator::make(Request::all(), [
            'facility-code' => 'required|alpha_num',
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }
        $facility = Facility::findByPublicID(Request::input('facility-code'));
        if (! $facility) {
            return Redirect::back()
                ->withError('That facility code is invalid.')
                ->withInput();
        }

        try {
            $application->setFacility($facility);
        } catch (Exception $e) {
            return Redirect::route('visiting.application.facility', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Facility selection saved!');
    }

    public function postFacility(ApplicationFacilitySelectedRequested $request, \App\Models\VisitTransfer\Application $application)
    {
        try {
            $application->setFacility(Facility::find(Request::input('facility_id')));
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
            $application->setStatement(Request::input('statement'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.statement', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Statement completed');
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
            $application->submit();
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
            $application->withdraw();
        } catch (Exception $e) {
            return Redirect::route('visiting.application.withdraw', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('Your application has been withdrawn! You can submit a new application as required.');
    }

    public function getView(\App\Models\VisitTransfer\Application $application)
    {
        $this->authorize('view', $application);

        $application->load('facility');

        $this->setTitle('View Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.view')
            ->with('application', $application);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::check() ? Auth::user()->visit_transfer_current : new \App\Models\VisitTransfer\Application;
    }
}
