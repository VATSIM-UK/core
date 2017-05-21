<?php

namespace App\Http\Requests\Mship\Account\Ban;

use App\Http\Requests\Request;

class CreateRequest extends Request
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'exists:mship_ban_reason,id',
            'ban_note_content' => 'required|min:5',
            'ban_reason_extra' => 'min:5',
        ];
    }
}
