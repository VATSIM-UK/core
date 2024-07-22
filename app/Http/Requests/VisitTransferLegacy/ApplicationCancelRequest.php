<?php

namespace App\Http\Requests\VisitTransferLegacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplicationCancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cancel_staff_note' => 'nullable|string|required',
            'cancel_reason' => 'nullable|string|required',
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
            'cancel_staff_note.string' => 'You must only provide alphanumeric text in your staff note.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $application = $this->route('application');

        return Gate::allows('cancel', $application);
    }
}
