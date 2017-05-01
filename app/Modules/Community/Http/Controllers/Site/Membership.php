<?php

namespace App\Modules\Community\Http\Controllers\Site;

use App\Modules\Community\Models\Group;
use App\Http\Controllers\BaseController;
use App\Modules\Community\Http\Requests\DeployToCommunityGroupRequest;

class Membership extends BaseController
{
    protected $redirectTo = 'mship/manage/dashboard';

    public function getDeploy()
    {
        $this->authorize('deploy', new \App\Modules\Community\Models\Membership());

        $defaultGroup = Group::isDefault()->first();
        $groups = Group::notDefault()->inRandomOrder()->get();

        return $this->viewMake('community::site.membership.deploy')
                    ->with('defaultGroup', $defaultGroup)
                    ->with('isDefaultGroupMember', $defaultGroup->hasMember(\Auth::user()))
                    ->with('groups', $groups);
    }

    public function postDeploy(DeployToCommunityGroupRequest $request)
    {
        $chosenGroup = Group::find($request->input('group'));

        \Auth::user()->addCommunityGroup($chosenGroup);

        if (!$chosenGroup->default) {
            \Auth::user()->syncWithDefaultCommunityGroup();
        }

        return redirect($this->redirectPath())->withSuccess("You have successfully been deployed to this '".$chosenGroup->name."' Group!");
    }
}
