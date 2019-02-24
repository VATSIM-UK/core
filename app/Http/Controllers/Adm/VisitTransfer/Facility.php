<?php

namespace App\Http\Controllers\Adm\VisitTransfer;

use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\VisitTransfer\FacilityCreateUpdateRequest;
use Redirect;

class Facility extends AdmController
{
    public function getList()
    {
        $faciilites = \App\Models\VisitTransfer\Facility::all();

        return $this->viewMake('visit-transfer.admin.facility.list')
            ->with('facilities', $faciilites);
    }

    public function getCreate()
    {
        $emails = collect();

        return $this->viewMake('visit-transfer.admin.facility.create_or_update')
            ->with('facility', new \App\Models\VisitTransfer\Facility)
            ->with('emails', $emails);
    }

    public function postCreate(FacilityCreateUpdateRequest $request)
    {
        $facility = \App\Models\VisitTransfer\Facility::create($this->getFacilityInputData());

        return Redirect::route('adm.visiting.facility')->withSuccess($facility->name.' has been created.');
    }

    public function getUpdate(\App\Models\VisitTransfer\Facility $facility)
    {
        $emails = $facility->emails()->get();

        return $this->viewMake('visit-transfer.admin.facility.create_or_update')
            ->with('facility', $facility)
            ->with('emails', $emails);
    }

    public function postUpdate(FacilityCreateUpdateRequest $request, \App\Models\VisitTransfer\Facility $facility)
    {
        $facility->update($this->getFacilityInputData());

        return Redirect::route('adm.visiting.facility')->withSuccess($facility->name.' has been updated.');
    }

    private function getFacilityInputData()
    {
        return \Input::only([
            'name', 'description', 'open', 'can_visit', 'can_transfer', 'training_required', 'training_team', 'training_spaces', 'stage_statement_enabled',
            'stage_reference_enabled', 'stage_reference_quantity', 'stage_checks', 'auto_acceptance', 'acceptance_emails', 'public',
        ]);
    }
}
