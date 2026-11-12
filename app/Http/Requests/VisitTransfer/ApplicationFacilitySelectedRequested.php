<?php

namespace App\Http\Requests\VisitTransfer;

use App\Models\VisitTransfer\Facility;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplicationFacilitySelectedRequested extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'facility_id' => 'required|exists:vt_facility,id',
            'permitted' => 'accepted',
        ];
    }

    /**
     * Get the messages that are displayed when a validation rule fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'facility_id.required' => 'You have chosen an invalid facility.',
            'permitted.accepted' => 'You are not permitted to apply to this facility.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('select-facility', Auth::user()->visit_transfer_current);
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();
        $data['permitted'] = false;

        $facility = Facility::find(array_get($data, 'facility_id', null));
        $application = Auth::user()->visit_transfer_current;

        if ($facility && $application && $application->facilityIsPermitted($facility)) {
            $data['permitted'] = true;
        }

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
