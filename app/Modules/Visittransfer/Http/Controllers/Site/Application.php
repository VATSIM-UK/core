<?php

namespace App\Modules\Visittransfer\Http\Controllers\Site;

use Auth;
use Input;
use Redirect;
use Exception;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\BaseController;
use App\Modules\Visittransfer\Models\Facility;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Http\Requests\ApplicationStartRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationSubmitRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationWithdrawRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationRefereeAddRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationRefereeDeleteRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationStatementSubmitRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationFacilitySelectedRequested;

class Application extends BaseController
{
    public function getStart($applicationType, $trainingTeam = 'atc')
    {
        $this->authorize('create', new \App\Modules\Visittransfer\Models\Application());

        if ($trainingTeam == 'pilot') {
            try {
                $application = $this->startApplication($applicationType, $trainingTeam);
            } catch (Exception $e) {
                return Redirect::route('visiting.landing')->withError($e->getMessage());
            }

            return Redirect::route('visiting.application.facility', [$application->public_id])->withSuccess('Application started! Please complete all sections to submit your application.');
        }

        return $this->viewMake('visittransfer::site.application.terms')
                    ->with('applicationType', $applicationType)
                    ->with('trainingTeam', $trainingTeam)
                    ->with('application', new \App\Modules\Visittransfer\Models\Application);
    }

    public function postStart(ApplicationStartRequest $request)
    {
        try {
            $application = $this->startApplication(Input::get('application_type'), Input::get('training_team'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.start', [Input::get('application_type')])->withError($e->getMessage());
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

    public function getContinue(\App\Modules\Visittransfer\Models\Application $application)
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

        return $this->viewMake('visittransfer::site.application.facility')
                    ->with('application', $this->getCurrentOpenApplicationForUser())
                    ->with('facilities', $this->getCurrentOpenApplicationForUser()->potential_facilities);
    }

    public function postFacility(ApplicationFacilitySelectedRequested $request, \App\Modules\Visittransfer\Models\Application $application)
    {
        try {
            $application->setFacility(Facility::find(Input::get('facility_id')));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.facility', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.continue', [$application->public_id])->withSuccess('Facility selection saved!');
    }

    public function getStatement(\App\Modules\Visittransfer\Models\Application $application)
    {
        $this->authorize('add-statement', $application);

        $application->load('facility');

        return $this->viewMake('visittransfer::site.application.statement')
                    ->with('application', $application);
    }

    public function postStatement(ApplicationStatementSubmitRequest $request, \App\Modules\Visittransfer\Models\Application $application)
    {
        try {
            $application->setStatement(Input::get('statement'));
        } catch (Exception $e) {
            return Redirect::route('visiting.application.statement', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.referees', [$application->public_id])->withSuccess('Statement completed');
    }

    public function getReferees(\App\Modules\Visittransfer\Models\Application $application)
    {
        $this->authorize('add-referee', $application);

        $application->load('referees.account');

        return $this->viewMake('visittransfer::site.application.referees')
                    ->with('application', $application);
    }

    public function postReferees(ApplicationRefereeAddRequest $request, \App\Modules\Visittransfer\Models\Application $application)
    {
        try {
            $application->addReferee(
                Account::findOrRetrieve(Input::get('referee_cid')),
                Input::get('referee_email'),
                Input::get('referee_relationship')
            );
        } catch (Exception $e) {
            return Redirect::route('visiting.application.referees', [$application->public_id])->withError($e->getMessage());
        }

        $redirectRoute = 'visiting.application.referees';

        if ($application->fresh()->number_references_required_relative == 0) {
            $redirectRoute = 'visiting.application.submit';
        }

        return Redirect::route($redirectRoute, [$application->public_id])->withSuccess('Referee '.Input::get('referee_cid').' added successfully! They will not be contacted until you submit your application.');
    }

    public function postRefereeDelete(ApplicationRefereeDeleteRequest $request, \App\Modules\Visittransfer\Models\Application $application, Reference $reference)
    {
        $reference->delete();

        return Redirect::route('visiting.application.referees', [$application->public_id])->withSuccess('Referee '.$reference->account->name.' deleted.');
    }

    public function getSubmit(\App\Modules\Visittransfer\Models\Application $application)
    {
        $this->authorize('submit-application', $application);

        return $this->viewMake('visittransfer::site.application.submission')
                    ->with('application', $application);
    }

    public function postSubmit(ApplicationSubmitRequest $request, \App\Modules\Visittransfer\Models\Application $application)
    {
        try {
            $application->submit();
        } catch (Exception $e) {
            return Redirect::route('visiting.application.submit', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.application.view', [$application->public_id])->withSuccess('Your application has been submitted! You will be notified when staff have reviewed the details.');
    }

    public function getWithdraw(\App\Modules\Visittransfer\Models\Application $application)
    {
        $this->authorize('withdraw-application', $application);

        return $this->viewMake('visittransfer::site.application.withdraw')
                    ->with('application', $application);
    }

    public function postWithdraw(ApplicationWithdrawRequest $request, \App\Modules\Visittransfer\Models\Application $application)
    {
        try {
            $application->withdraw();
        } catch (Exception $e) {
            return Redirect::route('visiting.application.withdraw', [$application->public_id])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('Your application has been withdrawn! You can submit a new application as required.');
    }

    public function getView(\App\Modules\Visittransfer\Models\Application $application)
    {
        $this->authorize('view-application', $application);

        $application->load('facility')->load('referees.account');

        return $this->viewMake('visittransfer::site.application.view')
                    ->with('application', $application);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::check() ? Auth::user()->visit_transfer_current : new \App\Modules\Visittransfer\Models\Application;
    }
}
