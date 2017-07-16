<?php

namespace App\Http\Requests\VisitTransfer;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplicationRefereeDeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Get the messages that are displayed when a validation rule fails.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('delete-referee', Auth::user()->visit_transfer_current);
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();

        // Extra.

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
