<?php
namespace App\Modules\Visittransfer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Application extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			//
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return false;
	}
}
