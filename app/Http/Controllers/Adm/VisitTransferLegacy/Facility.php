<?php

namespace App\Http\Controllers\Adm\VisitTransferLegacy;

use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\VisitTransferLegacy\FacilityCreateUpdateRequest;
use Illuminate\Support\Facades\Request;
use Redirect;

class Facility extends AdmController
{
    public function getList()
    {
        $faciilites = \App\Models\VisitTransferLegacy\Facility::all();

        return $this->viewMake('visit-transfer.admin.facility.list')
            ->with('facilities', $faciilites);
    }

    public function getCreate()
    {
        $emails = collect();

        return $this->viewMake('visit-transfer.admin.facility.create_or_update')
            ->with('facility', new \App\Models\VisitTransferLegacy\Facility)
            ->with('emails', $emails);
    }

    public function postCreate(FacilityCreateUpdateRequest $request)
    {
        $facility = \App\Models\VisitTransferLegacy\Facility::create($this->getFacilityInputData());

        return Redirect::route('adm.visiting.facility')->withSuccess($facility->name.' has been created.');
    }

    public function getUpdate(\App\Models\VisitTransferLegacy\Facility $facility)
    {
        $emails = $facility->emails()->get();

        return $this->viewMake('visit-transfer.admin.facility.create_or_update')
            ->with('facility', $facility)
            ->with('emails', $emails);
    }

    public function postUpdate(FacilityCreateUpdateRequest $request, \App\Models\VisitTransferLegacy\Facility $facility)
    {
        $facility->update($this->getFacilityInputData());

        return Redirect::route('adm.visiting.facility')->withSuccess($facility->name.' has been updated.');
    }

    private function getFacilityInputData()
    {
        return Request::only([
            'name', 'description', 'open', 'can_visit', 'can_transfer', 'training_required', 'training_team', 'training_spaces', 'stage_statement_enabled',
            'stage_reference_enabled', 'stage_reference_quantity', 'stage_checks', 'auto_acceptance', 'acceptance_emails', 'public',
        ]);
    }
}
