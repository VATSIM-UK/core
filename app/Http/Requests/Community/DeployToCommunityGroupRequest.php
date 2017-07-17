<?php

namespace App\Http\Requests\Community;

use App\Models\Community\Group;
use App\Models\Community\Membership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DeployToCommunityGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group' => [
                'required',
                'numeric',
                'min:1',
                Rule::exists('community_group', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
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
            'check.required' => 'You must specify a group to join.',
            'check.numeric' => 'You have selected an invalid group.',
            'check.min' => 'You have selected an invalid group.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('deploy', new Membership());
    }
}
