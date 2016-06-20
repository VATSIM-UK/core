<?php
namespace App\Modules\Visittransfer\Http\Requests;

use App\Models\Mship\Account\State;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Facility;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateFacilityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name"                     => "required|min:5",
            "description"              => "optional|min:25",
            "training_required"        => "required|boolean",
            "training_spaces"          => "required_if:training_required,1|numeric|min:0",
            "stage_statement_enabled"  => "required|boolean",
            "stage_reference_enabled"  => "required|boolean",
            "stage_reference_quantity" => "required_if:stage_reference_enabled,1|numeric|min:1",
            "stage_checks"             => "required|boolean",
            "auto_acceptance"          => "required|boolean",
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
            "name.required"                        => "You must provide a name of at least 5 characters.",
            "name.min"                             => "You must provide a name of at least 5 characters.",
            "description.min"                      => "Your description, if provided, must be at least 25 characters.",
            "training_required.required"           => "You must specify if training is required.",
            "training_required.boolean"            => "You must specify if training is required.",
            "training_spaces.required_if"          => "You must specify how many training spaces are available.",
            "training_spaces.numeric"              => "The number of training spaces must be numeric.",
            "training_spaces.min"                  => "You cannot have fewer than 0 training spaces.",
            "stage_statement_enabled.required"     => "You must specify if a statement of intent is required.",
            "stage_statement_enabled.boolean"      => "You must specify if a statement of intent is required.",
            "stage_reference_enabled.required"     => "You must specify if references are required.",
            "stage_reference_enabled.boolean"      => "You must specify if references are required.",
            "stage_reference_quantity.required_if" => "You must specify a quantity of references to be provided.",
            "stage_reference_quantity.numeric"     => "The number of references must be numeric.",
            "stage_reference_quantity.min"         => "The minimum quantity of references allowed is 1.",
            "stage_checks.required"                => "You must specify if the automated checks are to be performed.",
            "stage_checks.boolean"                 => "You must specify if the automated checks are to be performed.",
            "auto_acceptance.required"             => "You must specify if applications are to be automatically accepted.",
            "auto_acceptance.boolean"              => "You must specify if applications are to be automatically accepted.",
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: Add authorization.
        return true;
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();

        // Extra.

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
