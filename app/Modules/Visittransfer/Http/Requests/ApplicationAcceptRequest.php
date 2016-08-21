<?php
namespace App\Modules\Visittransfer\Http\Requests;

use App\Modules\Visittransfer\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplicationAcceptRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			"accept_staff_note" => "required|string|min:40",
		];
	}

	/**
	 * Get the messages that are displayed when a validation rule fails.
	 *
	 * @return array
	 */
	public function messages(){
		return [
			"accept_staff_note.required" => "You must provide a detailed staff note for this acceptance.",
			"accept_staff_note.string" => "You must only provide alphanumeric text in your staff note.",
			"accept_staff_note.min" => "When providing a staff note it must be a minimum of 40 characters.",
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		$application = $this->route("application");

		return Gate::allows("accept", $application);
	}
}
