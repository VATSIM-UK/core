<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaitingListAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'account_id.unique' => 'That account is already in this waiting list',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id' => ['required',
                Rule::unique('training_waiting_list_account')->where(function ($query) {
                    $query->where('list_id', $this->route('waitingList')->id);
                }),
            ],
        ];
    }
}
