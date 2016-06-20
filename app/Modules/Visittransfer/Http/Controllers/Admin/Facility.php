<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Http\Requests\CreateFacilityRequest;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Referee;
use Auth;
use Cache;

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

    public function postCreate(CreateFacilityRequest $request){
        \App\Modules\Visittransfer\Models\Facility::create(\Input::only([
            "name", "description", "training_required", "training_spaces", "stage_statement_enabled",
            "stage_reference_enabled", "stage_reference_quantity", "stage_checks", "auto_acceptance",
        ]));
    }

    public function getUpdate(\App\Modules\Visittransfer\Models\Facility $facility){
        return $this->viewMake("visittransfer::admin.facility.create_or_update")
                    ->with("facility", $facility);
    }

    public function postUpdate(CreateFacilityRequest $request){

    }

}
