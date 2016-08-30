<?php namespace App\Modules\Visittransfer\Http\Controllers\Site;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Http\Requests\ApplicationRefereeAddRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationFacilitySelectedRequested;
use App\Modules\Visittransfer\Http\Requests\ApplicationRefereeDeleteRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationStartRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationSubmitRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationStatementSubmitRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationWithdrawRequest;
use App\Modules\Visittransfer\Models\Facility;
use App\Modules\Visittransfer\Models\Reference;
use Auth;
use Exception;
use Illuminate\Support\Facades\Gate;
use Input;
use Redirect;

class Application extends BaseController
{
    private $application = null;

    public function __construct(){
        $this->application = $this->getCurrentOpenApplicationForUser();
    }

    public function getStart($applicationType, $trainingTeam="atc")
    {
        $this->authorize("create", new \App\Modules\Visittransfer\Models\Application());

        if($trainingTeam == "pilot"){
            try {
                $application = $this->startApplication($applicationType, $trainingTeam);
            } catch(Exception $e){
                return Redirect::route("visiting.dashboard")->withError($e->getMessage());
            }

            return Redirect::route("visiting.application.facility")->withSuccess("Application started! Please complete all sections to submit your application.");
        }

        return $this->viewMake("visittransfer::site.application.terms")
                    ->with("applicationType", $applicationType)
                    ->with("trainingTeam", $trainingTeam)
                    ->with("application", new \App\Modules\Visittransfer\Models\Application);
    }

    public function postStart(ApplicationStartRequest $request)
    {
        try {
            $application = $this->startApplication(Input::get("application_type"), Input::get("training_team"));
        } catch(Exception $e){
            return Redirect::route("visiting.application.start", [Input::get("application_type")])->withError($e->getMessage());
        }

        return Redirect::route("visiting.application.facility")->withSuccess("Application started! Please complete all sections to submit your application.");
    }

    private function startApplication($type, $team){
        return Auth::user()->createVisitingTransferApplication([
            "type" => $type,
            "training_team" => $team,
        ]);
    }
    
    public function getContinue(){
        if(Gate::allows("select-facility", Auth::user()->visitTransferCurrent())){
            return Redirect::route("visiting.application.facility");
        }

        if(Gate::allows("add-statement", Auth::user()->visitTransferCurrent()) && Auth::user()->visitTransferCurrent()->statement == null){
            return Redirect::route("visiting.application.statement");
        }

        if(Gate::allows("add-referee", Auth::user()->visitTransferCurrent()) && Auth::user()->visitTransferCurrent()->number_references_required_relative > 0){
            return Redirect::route("visiting.application.referees");
        }

        if(Gate::allows("submit-application", Auth::user()->visitTransferCurrent())){
            return Redirect::route("visiting.application.submit");
        }

        if(Auth::user()->visitTransferCurrent() != null){
            return Redirect::route("visiting.application.view", [Auth::user()->visitTransferCurrent()->public_id]);
        }

        return Redirect::route("visiting.dashboard");
    }

    public function getFacility()
    {
        $this->authorize("select-facility", $this->getCurrentOpenApplicationForUser());

        return $this->viewMake("visittransfer::site.application.facility")
                    ->with("application", $this->getCurrentOpenApplicationForUser())
                    ->with("facilities", $this->getCurrentOpenApplicationForUser()->potential_facilities);
    }

    public function postFacility(ApplicationFacilitySelectedRequested $request)
    {
        try {
            $this->application->setFacility(Facility::find(Input::get("facility_id")));
        } catch(Exception $e){
            return Redirect::route("visiting.application.facility")->withError($e->getMessage());
        }

        return Redirect::route("visiting.application.continue")->withSuccess("Facility selection saved!");
    }

    public function getStatement()
    {
        $this->authorize("add-statement", $this->application);

        $this->application->load("facility");

        return $this->viewMake("visittransfer::site.application.statement")
                    ->with("application", $this->application);
    }

    public function postStatement(ApplicationStatementSubmitRequest $request){
        try {
            $this->application->setStatement(Input::get("statement"));
        } catch(Exception $e){
            return Redirect::route("visiting.application.statement")->withError($e->getMessage());
        }

        return Redirect::route("visiting.application.referees")->withSuccess("Statement completed");
    }

    public function getReferees(){
        $this->authorize("add-referee", $this->application);

        $this->application->load("referees.account");

        return $this->viewMake("visittransfer::site.application.referees")
                    ->with("application", $this->application);
    }

    public function postReferees(ApplicationRefereeAddRequest $request){
        try {
            $this->application->addReferee(
                Account::findOrRetrieve(Input::get("referee_cid")),
                Input::get("referee_email"),
                Input::get("referee_relationship")
            );
        } catch(Exception $e){
            return Redirect::route("visiting.application.referees")->withError($e->getMessage());
        }

        $redirectRoute = "visiting.application.referees";

        if($this->application->fresh()->number_references_required_relative == 0){
            $redirectRoute = "visiting.application.submit";
        }

        return Redirect::route($redirectRoute)->withSuccess("Referee ". Input::get("referee_cid") . " added successfully! They will not be contacted until you submit your application.");
    }

    public function postRefereeDelete(ApplicationRefereeDeleteRequest $request, Reference $reference){
        $reference->delete();

        return Redirect::route("visiting.application.referees")->withSuccess("Referee " . $reference->account->name . " deleted.");
    }

    public function getSubmit(){
        $this->authorize("submit-application", $this->application);

        return $this->viewMake("visittransfer::site.application.submission")
                    ->with("application", $this->application);
    }

    public function postSubmit(ApplicationSubmitRequest $request){
        try {
            $this->application->submit();
        } catch(Exception $e){
            return Redirect::route("visiting.application.submit")->withError($e->getMessage());
        }

        return Redirect::route("visiting.application.view", [$this->application->public_id])->withSuccess("Your application has been submitted! You will be notified when staff have reviewed the details.");
    }

    public function getWithdraw(){
        $this->authorize("withdraw-application", $this->application);

        return $this->viewMake("visittransfer::site.application.withdraw")
                    ->with("application", $this->application);
    }

    public function postWithdraw(ApplicationWithdrawRequest $request){
        try {
            $this->application->withdraw();
        } catch(Exception $e){
            return Redirect::route("visiting.application.withdraw")->withError($e->getMessage());
        }

        return Redirect::route("visiting.application.view", [$this->application->public_id])->withSuccess("Your application has been withdrawn! You can submit a new application as required.");
    }

    public function getView(\App\Modules\Visittransfer\Models\Application $application){
        $this->authorize("view-application", $application);

        $application->load("facility")->load("referees.account");

        return $this->viewMake("visittransfer::site.application.view")
                    ->with("application", $application);
    }

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::check() ? Auth::user()->visitTransferCurrent() : new \App\Modules\Visittransfer\Models\Application;
    }
}
