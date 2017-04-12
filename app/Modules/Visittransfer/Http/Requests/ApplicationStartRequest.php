<?php

namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Visittransfer\Models\Application;

class ApplicationStartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'terms_read' => 'required',
            'terms_one_hour' => 'required',
            'terms_hours_minimum' => 'required',
            'terms_hours_minimum_relevant' => 'required',
            'terms_recent_transfer' => 'required',
            'terms_90_day' => 'required',
            'terms_not_staff' => 'required_if:application_type,'.\App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER,
            'application_type' => 'required|in:'.\App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER.','.\App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
            'training_team' => 'required|in:pilot,atc',
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
            'terms_read.required' => 'You are required to read the VTCP.',
            'terms_one_hour.required' => 'You must agree to complete your application within 1 hour.',
            'terms_hours_minimum.required' => 'You must confirm that you have the minimum number of hours at your present rating.',
            'terms_hours_minimum_relevant.required' => 'The hours you have achieved, must be at a relevant rating.',
            'terms_recent_transfer.required' => 'You are only permitted to visit/transfer once every 90 days.',
            'terms_90_day.required' => 'You must agree that you will be returned to your former region/division if you do not complete your induction training.',
            'terms_not_staff.required_if' => 'You cannot be staff in another region or division if your transfer is successful.',
            'application_type.required' => 'You have requested an invalid application type.',
            'application_type.in' => 'You have requested an invalid application type.',
            'training_team.required' => 'You have requested an invalid training team.',
            'training_team.in' => 'You have requested an invalid training team.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', new Application());
    }
}
