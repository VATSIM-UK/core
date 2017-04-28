<?php

namespace App\Http\Requests\Mship\Note\Type;

use Auth;
use App\Http\Requests\Request;
use App\Models\Mship\Note\Type;

class CreateEditNoteType extends Request
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
        $currentNoteType = $this->route('mshipNoteType');

        $currentNoteTypeId = null;
        if ($currentNoteType) {
            $currentNoteTypeId = $currentNoteType->id;
        }

        return [
            'name' => 'required|min:5',
            'short_code' => 'min:5|unique:mship_note_type,short_code,'.$currentNoteTypeId.',note_type_id',
            'is_available' => 'required|boolean',
            'is_default' => 'required|boolean',
            'colour_code' => 'required|in:success,danger,warning,info,primary',
        ];
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();

        $currentNoteType = $this->route('mshipNoteType');

        if ($currentNoteType != null && $currentNoteType->exists) {
            if (Auth::user()->hasPermission('adm/mship/note/type/default')) {
                $data['is_default'] = array_get($data, 'is_default', $currentNoteType->is_default);
            } else {
                $data['is_default'] = $currentNoteType->is_default;
            }
        } else {
            if (Auth::user()->hasPermission('adm/mship/note/type/default')) {
                $data['is_default'] = array_get($data, 'is_default', false);
            } else {
                $data['is_default'] = false;
            }
        }

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
