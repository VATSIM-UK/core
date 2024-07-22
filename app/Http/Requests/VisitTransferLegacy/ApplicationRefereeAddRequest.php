<?php

namespace App\Http\Requests\VisitTransferLegacy;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplicationRefereeAddRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'referee_cid' => 'required|numeric|min:800000|max:2000000',
            'referee_email' => 'required|email',
            'referee_relationship' => 'required|string',
            'no_self_reference' => 'accepted',
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
            'referee_cid.required' => 'You must enter a CID.',
            'referee_cid.min' => 'You cannot enter a CID this low.',
            'referee_cid.max' => 'You cannot enter a CID this high.',
            'referee_cid.unique' => 'You have already added this referee.',
            'referee_email.required' => "You must provide your referee's staff email address.",
            'referee_email.email' => 'This is not a valid email address.',
            'referee_relationship.required' => "You must provide your referee's staff position.",
            'referee_relationship.string' => 'You have provided an invalid staff title.',
            'no_self_reference.accepted' => 'You cannot be your own referee.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('add-referee', Auth::user()->visit_transfer_current);
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();
        $data['no_self_reference'] = true;

        if (Auth::user()->id == array_get($data, 'referee_cid', null)) {
            $data['no_self_reference'] = false;
        }

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
