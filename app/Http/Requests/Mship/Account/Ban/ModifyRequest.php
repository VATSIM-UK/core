<?php

namespace App\Http\Requests\Mship\Account\Ban;

use App\Http\Requests\Request;

class ModifyRequest extends Request
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
            'finish_date' => 'required|date_format:Y-m-d',
            'finish_time' => 'required|date_format:H:i',
            'period_finish' => 'required|date_format:Y-m-d H:i:s',
            'reason_extra' => 'required|min:5',
            'note' => 'required|min:5',
        ];
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();
        $data['period_finish'] = array_get($data, 'finish_date', null).' '.array_get($data, 'finish_time', null).':00';
        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
