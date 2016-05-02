<?php

namespace App\Modules\Visittransfer\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Http\Requests\SelectFacilityApplicationRequest;
use App\Modules\Visittransfer\Http\Requests\StartApplicationRequest;
use App\Modules\Visittransfer\Http\Requests\SubmitStatementApplicationRequest;
use App\Modules\Visittransfer\Models\Facility;
use Auth;
use Input;
use Redirect;

class Application extends BaseController
{

    public function getStart($applicationType)
    {
        $this->authorize("create", new \App\Modules\Visittransfer\Models\Application());

        return $this->viewMake("visittransfer::application.terms")
                    ->with("applicationType", $applicationType);
    }

    public function postStart(StartApplicationRequest $request)
    {
        $application = Auth::user()->visitTransferApplications()->create([
            "type" => Input::get("application_type"),
        ]);

        return Redirect::route("visiting.application.facility");
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

    private function getCurrentOpenApplicationForUser()
    {
        return Auth::user()->visitTransferCurrent();
    }
}
