<?php
namespace App\Modules\Visittransfer\Http\Requests;

use App\Models\Mship\Account\State;
use App\Modules\Visittransfer\Models\Application;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StartApplicationRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			"terms_read" => "required",
			"terms_hours_minimum" => "required",
			"terms_hours_minimum_relevant" => "required",
			"terms_recent_transfer" => "required",
			"terms_90_day" => "required",
			"terms_not_staff" => "required_if:application_type,".\App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER,
			"application_type" => "in:".\App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER.",".\App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
		];
	}

	/**
	 * Get the messages that are displayed when a validation rule fails.
	 *
	 * @return array
	 */
	public function messages(){
		return [
			"terms_read.required" => "You are required to read the VTCP.",
			"terms_hours_minimum.required" => "You must confirm that you have the minimum number of hours at your present rating.",
			"terms_hours_minimum_relevant.required" => "The hours you have achieved, must be at a relevant rating.",
			"terms_recent_transfer.required" => "You are only permitted to visit/transfer once every 90 days.",
			"terms_90_day.required" => "You must agree that you will be returned to your former region/division if you do not complete your induction training.",
			"terms_not_staff.required_if" => "You cannot be staff in another region or division if your transfer is successful.",
			"application_type.in" => "You have requested an invalid application type.",
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return Gate::allows("create", new Application());
	}
}
