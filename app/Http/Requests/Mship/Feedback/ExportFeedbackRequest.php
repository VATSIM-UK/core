<?php

namespace App\Http\Requests\Mship\Feedback;

use App\Models\Mship\Feedback\Form;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Request;

class ExportFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $form = Form::whereSlug(Request::route('slug'))->first();
        if (! $form) {
            return false;
        }
        if (! Auth::user()->can('use-permission', 'adm/mship/feedback/list/*') && ! Auth::user()->can('use-permission', 'adm/mship/feedback/list/'.$form->slug)) {
            return false;
        }

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
            'from' => 'required|date_format:Y/m/d',
            'to' => 'required|date_format:Y/m/d',
            'include_actioned' => 'nullable|boolean',
            'include_unactioned' => 'nullable|boolean',
            'include_target' => 'nullable|boolean',
        ];
    }

    /**
     * Get the error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'from.required' => 'You must supply a start date!',
            'from.date_format' => 'The start date entered was in an incorrect format!',
            'to.required' => 'You must supply a end date!',
            'to.date_format' => 'The end date entered was in an incorrect format!',

            'include_actioned.boolean' => 'The include actioned checkbox must be on or off!',
            'include_unactioned.boolean' => 'The include unactioned checkbox must be on or off!',
            'include_target.boolean' => 'The include target checkbox must be on or off!',
        ];
    }
}
