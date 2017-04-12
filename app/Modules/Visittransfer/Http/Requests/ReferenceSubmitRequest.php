<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Visittransfer\Models\Application;

class ReferenceSubmitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reference_relationship' => 'required',
            'reference_hours_minimum' => 'required',
            'reference_recent_transfer' => 'required',
            'reference_not_staff' => 'required_if:application_type,'.Application::TYPE_TRANSFER,
            'reference' => 'required|string|min:50|max:1000',
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
            'reference_relationship.required' => 'You are required to confirm your relationship with the applicant.',
            'reference_hours_minimum.required' => 'You must confirm that the candidate has the minimum number of hours at their present rating.',
            'reference_recent_transfer.required' => 'You must confirm that the candidate has not transfered region, division or VACC within the last 90 days.',
            'reference_not_staff.required_if' => 'You must confirm that the candidate will not be staff in their home division if their application is successful.',

            'reference.required' => 'You must write a reference.',
            'reference.string' => 'You must only provide alphanumeric text in your reference.',
            'reference.min' => 'The minimum length reference is 50 characters.',
            'reference.max' => 'The maximum length reference is 1000 characters.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $token = $this->route('token');
        $reference = $token->related;

        return Gate::allows('complete', $reference);
    }
}
