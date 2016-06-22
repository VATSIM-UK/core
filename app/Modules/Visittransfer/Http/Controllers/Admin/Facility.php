<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Http\Requests\FacilityCreateUpdateRequest;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Auth;
use Cache;
use Redirect;

class Facility extends AdmController
{

    public function getList()
    {
        $faciilites = \App\Modules\Visittransfer\Models\Facility::all();

        return $this->viewMake("visittransfer::admin.facility.list")
                    ->with("facilities", $faciilites);
    }

    public function getCreate(){
        return $this->viewMake("visittransfer::admin.facility.create_or_update")
                    ->with("facility", new \App\Modules\Visittransfer\Models\Facility);
    }

    public function postCreate(FacilityCreateUpdateRequest $request){
        $facility = \App\Modules\Visittransfer\Models\Facility::create($this->getFacilityInputData());

        return Redirect::route("visiting.admin.facility")->withSuccess($facility->name . " has been created.");
    }

    public function getUpdate(\App\Modules\Visittransfer\Models\Facility $facility){
        return $this->viewMake("visittransfer::admin.facility.create_or_update")
                    ->with("facility", $facility);
    }

    public function postUpdate(FacilityCreateUpdateRequest $request, \App\Modules\Visittransfer\Models\Facility $facility){
        $facility->update($this->getFacilityInputData());

        return Redirect::route("visiting.admin.facility")->withSuccess($facility->name . " has been updated.");
    }

    private function getFacilityInputData(){
        return \Input::only([
            "name", "description", "training_required", "training_spaces", "stage_statement_enabled",
            "stage_reference_enabled", "stage_reference_quantity", "stage_checks", "auto_acceptance",
        ]);
    }

}
