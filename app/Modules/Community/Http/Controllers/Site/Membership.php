<?php

namespace App\Modules\Community\Http\Controllers\Site;

use App\Modules\Community\Models\Group;
use App\Http\Controllers\BaseController;
use App\Modules\Community\Http\Requests\DeployToCommunityGroupRequest;

class Membership extends BaseController
{
    public function getDeploy()
    {
        if (\Carbon\Carbon::now()->lt(\Carbon\Carbon::parse('15th December 2016 00:00:00 GMT'))) {
            return redirect()->route('mship.manage.dashboard')->withError('The Community Groups feature is not yet enabled.');
        }

        $this->authorize('deploy', new \App\Modules\Community\Models\Membership());

        $defaultGroup = Group::isDefault()->first();
        $groups       = Group::notDefault()->inRandomOrder()->get();

        return $this->viewMake('community::site.membership.deploy')
                    ->with('defaultGroup', $defaultGroup)
                    ->with('isDefaultGroupMember', $defaultGroup->hasMember(\Auth::user()))
                    ->with('groups', $groups);
    }

    public function postDeploy(DeployToCommunityGroupRequest $request)
    {
        $chosenGroup = Group::find($request->input('group'));

        \Auth::user()->addCommunityGroup($chosenGroup);

        if (! $chosenGroup->default) {
            \Auth::user()->syncWithDefaultCommunityGroup();
        }

        return redirect()->route('mship.manage.dashboard')->withSuccess("You have successfully been deployed to this '".$chosenGroup->name."' Group!");
    }
}
