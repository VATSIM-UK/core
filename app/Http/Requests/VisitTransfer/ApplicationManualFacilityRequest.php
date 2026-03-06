<?php

namespace App\Http\Requests\VisitTransfer;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationManualFacilityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'facility-code' => 'required|alpha_num',
        ];
    }
}
