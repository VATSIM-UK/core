<?php

namespace App\Modules\Visittransfer\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Http\Requests\AddRefereeApplicationRequest;
use App\Modules\Visittransfer\Http\Requests\SelectFacilityApplicationRequest;
use App\Modules\Visittransfer\Http\Requests\StartApplicationRequest;
use App\Modules\Visittransfer\Http\Requests\SubmitStatementApplicationRequest;
use App\Modules\Visittransfer\Models\Facility;
use App\Modules\Visittransfer\Models\Referee;
use Auth;
use Illuminate\Support\Facades\Gate;
use Input;
use Redirect;

class Application extends BaseController
{

    public function getStart($applicationType)
    {
        $this->authorize("create", new \App\Modules\Visittransfer\Models\Application());

        return $this->viewMake("visittransfer::application.terms")
                    ->with("applicationType", $applicationType)
                    ->with("application", new \App\Modules\Visittransfer\Models\Application);
    }

    public function postStart(StartApplicationRequest $request)
    {
        $application = Auth::user()->visitTransferApplications()->create([
            "type" => Input::get("application_type"),
        ]);

        return Redirect::route("visiting.application.facility");
    }
    
    public function getContinue(){
        if(Gate::allows("select-facility", Auth::user()->visitTransferCurrent())){
            return Redirect::route("visiting.application.facility");
        }

        if(Gate::allows("add-statement", Auth::user()->visitTransferCurrent())){
            return Redirect::route("visiting.application.statement");
        }
    }

    public function getFacility()
    {
        $this->authorize("select-facility", $this->getCurrentOpenApplicationForUser());

        if ($this->getCurrentOpenApplicationForUser()->is_visit) {
            $facilities = Facility::all();
        } else {
            $facilities = Facility::trainingRequired()->get();
        }

        return $this->viewMake("visittransfer::application.facility")
                    ->with("application", $this->getCurrentOpenApplicationForUser())
                    ->with("facilities", $facilities);
    }

    public function postFacility(SelectFacilityApplicationRequest $request)
    {
        $application = $this->getCurrentOpenApplicationForUser();

        $application->facility_id = Input::get("facility_id");

        $application->save();

        return Redirect::route("visiting.application.statement");
    }

    public function getStatement()
    {
        $this->authorize("add-statement", $this->getCurrentOpenApplicationForUser());

        $application = $this->getCurrentOpenApplicationForUser();
        $application->load("facility");

        return $this->viewMake("visittransfer::application.statement")
                    ->with("application", $this->getCurrentOpenApplicationForUser());
    }

    public function postStatement(SubmitStatementApplicationRequest $request){
        $application = $this->getCurrentOpenApplicationForUser();

        $application->statement = Input::get("statement");

        $application->save();

        return Redirect::route("visiting.application.referees");
    }

    public function getReferees(){
        return $this->viewMake("visittransfer::application.referees")
                    ->with("application", $this->getCurrentOpenApplicationForUser()->load("referees"));
    }

    public function postReferees(AddRefereeApplicationRequest $request){
        $application = $this->getCurrentOpenApplicationForUser();

        $referee = new Referee([
            "email" => Input::get("referee_email"),
            "relationship" => Input::get("referee_position"),
        ]);

        $application->referees()->save($referee);

        $refereeAccount = Account::find(Input::get("referee_cid"));
        $refereeAccount->visitTransferReferee()->save($referee);

        return Redirect::route("visiting.application.referees")->withSuccess("Referee added successfully! They will not be contacted until you submit your application.");
    }

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::user()->visitTransferCurrent();
    }
}
