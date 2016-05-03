<?php
namespace App\Modules\Visittransfer\Http\Requests;

use App\Models\Mship\Account\State;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Facility;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AddRefereeApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "referee_cid"      => "required|numeric|min:800000|max:2000000",
            "referee_email"    => "required|email",
            "referee_relationship" => "required|string",
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
            "referee_cid.required"      => "You must enter a CID.",
            "referee_cid.min"           => "You cannot enter a CID this low.",
            "referee_cid.max"           => "You cannot enter a CID this high.",
            "referee_cid.unique"        => "You have already added this referee.",
            "referee_email.required"    => "You must provide your referee's staff email address.",
            "referee_email.email"       => "This is not a valid email address.",
            "referee_relationship.required" => "You must provide your referee's staff position.",
            "referee_relationship.string"   => "You have provided an invalid staff title.",
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows("add-referee", Auth::user()->visitTransferCurrent());
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();

        // Extra.

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
