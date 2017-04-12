<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationStatementSubmitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'statement' => 'required|string|min:50|max:1000',
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
            'statement.required' => 'You must write a supporting statement.',
            'statement.string' => 'You must only provide text in your supporting statement.',
            'statement.min' => 'The minimum length statement is 50 characters.',
            'statement.max' => 'The maximum length statement is 1000 characters.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('add-statement', Auth::user()->visit_transfer_current);
    }
}
