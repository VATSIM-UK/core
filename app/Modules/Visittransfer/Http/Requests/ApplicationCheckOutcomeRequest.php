<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Visittransfer\Models\Application;

class ApplicationCheckOutcomeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check' => 'required|in:90_day,50_hours',
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
            'check.required' => 'You must specify which check you wish to set the outcome for.',
            'check.in' => 'The check you specified does not exist.',
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

        return Gate::allows('check-outcome', $application);
    }
}
