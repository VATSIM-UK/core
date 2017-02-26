<?php

namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use Redirect;
use App\Http\Controllers\Adm\AdmController;
use App\Modules\Visittransfer\Http\Requests\FacilityCreateUpdateRequest;

class Facility extends AdmController
{
    public function getList()
    {
        $faciilites = \App\Modules\Visittransfer\Models\Facility::all();

        return $this->viewMake('visittransfer::admin.facility.list')
                    ->with('facilities', $faciilites);
    }

    public function getCreate()
    {
        $emails = collect();

        return $this->viewMake('visittransfer::admin.facility.create_or_update')
                    ->with('facility', new \App\Modules\Visittransfer\Models\Facility)
                    ->with('emails', $emails);
    }

    public function postCreate(FacilityCreateUpdateRequest $request)
    {
        $facility = \App\Modules\Visittransfer\Models\Facility::create($this->getFacilityInputData());

        return Redirect::route('visiting.admin.facility')->withSuccess($facility->name.' has been created.');
    }

    public function getUpdate(\App\Modules\Visittransfer\Models\Facility $facility)
    {
        $emails = $facility->emails()->get();

        return $this->viewMake('visittransfer::admin.facility.create_or_update')
                    ->with('facility', $facility)
                    ->with('emails', $emails);
    }

    public function postUpdate(FacilityCreateUpdateRequest $request, \App\Modules\Visittransfer\Models\Facility $facility)
    {
        $facility->update($this->getFacilityInputData());

        return Redirect::route('visiting.admin.facility')->withSuccess($facility->name.' has been updated.');
    }

    private function getFacilityInputData()
    {
        return \Input::only([
            'name', 'description', 'can_visit', 'can_transfer', 'training_required', 'training_team', 'training_spaces', 'stage_statement_enabled',
            'stage_reference_enabled', 'stage_reference_quantity', 'stage_checks', 'auto_acceptance', 'acceptance_emails',
        ]);
    }
}
