<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Visittransfer\Models\Application;

class ApplicationSettingToggleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'setting' => 'required|in:training_required,statement_required,references_required,should_perform_checks,will_auto_accept',
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
            'setting.required' => 'You must specify which setting you wish to toggle.',
            'setting.in' => 'The setting you specified does not exist.',
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

        return Gate::allows('setting-toggle', $application);
    }
}
