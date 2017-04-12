<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class ReferenceRejectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rejection_reason' => 'required',
            'rejection_reason_extra' => 'required_if:rejection_reason,other',
            'rejection_staff_note' => 'string|min:20',
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
            'rejection_reason.required' => 'You must choose a rejection reason for this reference.',
            'rejection_reason_extra.required_if' => "If you choose the 'other' rejection code, you must provide a reason.",

            'rejection_staff_note.string' => 'You must only provide alphanumeric text in your staff note.',
            'rejection_staff_note.min' => 'When providing a staff note it must be a minimum of 20 characters.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $reference = $this->route('reference');

        return Gate::allows('reject', $reference);
    }
}
