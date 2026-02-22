<?php

namespace App\Http\Requests\Mship\Feedback;

use App\Http\Requests\Request;

class SelectFeedbackFormRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'feedback_type' => 'required|exists:mship_feedback_forms,slug',
        ];
    }
}
