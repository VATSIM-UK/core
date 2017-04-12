<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Visittransfer\Models\Facility;

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
        $data['permitted'] = true;

        $facility = Facility::find(array_get($data, 'facility_id', null));

        if (Auth::user()->visit_transfer_current->is_transfer && !$facility->training_required) {
            $data['permitted'] = false;
        }

        if (Auth::user()->visit_transfer_current->is_transfer && !$facility->can_transfer) {
            $data['permitted'] = false;
        }

        if (Auth::user()->visit_transfer_current->is_visit && !$facility->can_visit) {
            $data['permitted'] = false;
        }

        if (strcasecmp($facility->training_team, Auth::user()->visit_transfer_current->training_team) != 0) {
            $data['permitted'] = false;
        }

        if ($facility->training_spaces < 1 && $facility->training_spaces !== null && $facility->training_required) {
            $data['permitted'] = false;
        }

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
