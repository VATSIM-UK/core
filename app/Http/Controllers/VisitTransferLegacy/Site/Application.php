<?php

namespace App\Http\Controllers\VisitTransferLegacy\Site;

use App\Exceptions\Mship\InvalidCIDException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransferLegacy\ApplicationFacilitySelectedRequested;
use App\Http\Requests\VisitTransferLegacy\ApplicationRefereeAddRequest;
use App\Http\Requests\VisitTransferLegacy\ApplicationRefereeDeleteRequest;
use App\Http\Requests\VisitTransferLegacy\ApplicationStartRequest;
use App\Http\Requests\VisitTransferLegacy\ApplicationStatementSubmitRequest;
use App\Http\Requests\VisitTransferLegacy\ApplicationSubmitRequest;
use App\Http\Requests\VisitTransferLegacy\ApplicationWithdrawRequest;
use App\Models\Mship\Account;
use App\Models\VisitTransferLegacy\Facility;
use App\Models\VisitTransferLegacy\Reference;
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
        $this->authorize('create', new \App\Models\VisitTransferLegacy\Application());

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
            ->with('application', new \App\Models\VisitTransferLegacy\Application);
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

    public function getContinue(\App\Models\VisitTransferLegacy\Application $application)
    {
        if (Gate::allows('select-facility', $application)) {
            return Redirect::route('visiting.application.facility', [$application->public_id]);
        }

        if (Gate::allows('add-statement', $application) && $application->statement == null) {
            return Redirect::route('visiting.application.statement', [$application->public_id]);
        }

        if (Gate::allows('add-referee', $application) && $application->number_references_required_relative > 0) {
            return Redirect::route('visiting.application.referees', [$application->public_id]);
        }

        if (Gate::allows('submit-application', $application)) {
            return Redirect::route('visiting.application.submit', [$application->public_id]);
        }

        if (Gate::allows('view-application', $application)) {
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

    public function postManualFacility(Request $request, \App\Models\VisitTransferLegacy\Application $application)
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

    public function postFacility(ApplicationFacilitySelectedRequested $request, \App\Models\VisitTransferLegacy\Application $application)
    {
        try {
            $application->setFacility(Facility::find(Request::input('facility_id')));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.facility', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Facility selection saved!');
    }

    public function getStatement(\App\Models\VisitTransferLegacy\Application $application)
    {
        $this->authorize('add-statement', $application);

        $application->load('facility');

        $this->setTitle('Statement - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.statement')
            ->with('application', $application);
    }

    public function postStatement(ApplicationStatementSubmitRequest $request, \App\Models\VisitTransferLegacy\Application $application)
    {
        try {
            $application->setStatement(Request::input('statement'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.statement', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Statement completed');
    }

    public function getReferees(\App\Models\VisitTransferLegacy\Application $application)
    {
        $this->authorize('add-referee', $application);

        $application->load('referees.account');

        $this->setTitle('Referees - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.referees')
            ->with('application', $application);
    }

    public function postReferees(ApplicationRefereeAddRequest $request, \App\Models\VisitTransferLegacy\Application $application)
    {
        // Check if the CID is in the home region
        try {
            $referee = Account::findOrRetrieve(Request::input('referee_cid'));
        } catch (InvalidCIDException $e) {
            return Redirect::back()
                ->withError("There doesn't seem to be a VATSIM user with that ID.")
                ->withInput();
        }

        try {
            if ($referee->primary_permanent_state->pivot->region != Auth::user()->primary_permanent_state->pivot->region) {
                return Redirect::back()
                    ->withError('Your referee must be in your home region.')
                    ->withInput();
            }
        } catch (ErrorException $e) {
            // If we don't have this data, we shouldn't penalise the applicant at this point.
        }

        try {
            $application->addReferee(
                $referee,
                Request::input('referee_email'),
                Request::input('referee_relationship')
            );
        } catch (Exception $e) {
            return Redirect::route('visiting.application.referees', [$application->public_id])->withError($e->getMessage());
        }

        $redirectRoute = 'visiting.application.referees';

        if ($application->fresh()->number_references_required_relative == 0) {
            $redirectRoute = 'visiting.application.submit';
        }

        return Redirect::route($redirectRoute, [$application->public_id])->withSuccess('Referee '.Request::input('referee_cid').' added successfully! They will not be contacted until you submit your application.');
    }

    public function postRefereeDelete(ApplicationRefereeDeleteRequest $request, \App\Models\VisitTransferLegacy\Application $application, Reference $reference)
    {
        $reference->delete();

        return Redirect::route('visiting.application.referees', [$application->public_id])->withSuccess('Referee '.$reference->account->name.' deleted.');
    }

    public function getSubmit(\App\Models\VisitTransferLegacy\Application $application)
    {
        $this->authorize('submit-application', $application);

        $this->setTitle('Submit - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.submission')
            ->with('application', $application);
    }

    public function postSubmit(ApplicationSubmitRequest $request, \App\Models\VisitTransferLegacy\Application $application)
    {
        try {
            $application->submit();
        } catch (Exception $e) {
            return Redirect::route('visiting.application.submit', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.view', [$application->public_id])->withSuccess('Your application has been submitted! You will be notified when staff have reviewed the details.');
    }

    public function getWithdraw(\App\Models\VisitTransferLegacy\Application $application)
    {
        $this->authorize('withdraw-application', $application);

        $this->setTitle('Withdraw - Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.withdraw')
            ->with('application', $application);
    }

    public function postWithdraw(ApplicationWithdrawRequest $request, \App\Models\VisitTransferLegacy\Application $application)
    {
        try {
            $application->withdraw();
        } catch (Exception $e) {
            return Redirect::route('visiting.application.withdraw', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('Your application has been withdrawn! You can submit a new application as required.');
    }

    public function getView(\App\Models\VisitTransferLegacy\Application $application)
    {
        $this->authorize('view-application', $application);

        $application->load('facility')->load('referees.account');

        $this->setTitle('View Visit/Transfer Application');

        return $this->viewMake('visit-transfer.site.application.view')
            ->with('application', $application);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::check() ? Auth::user()->visit_transfer_current : new \App\Models\VisitTransferLegacy\Application;
    }
}
