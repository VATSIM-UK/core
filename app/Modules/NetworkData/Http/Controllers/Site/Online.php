<?php

namespace App\Modules\Networkdata\Http\Controllers\Site;

use App\Modules\Community\Models\Group;
use App\Http\Controllers\BaseController;
use App\Modules\Community\Http\Requests\DeployToCommunityGroupRequest;
use App\Modules\NetworkData\Models\Atc;

class Online extends BaseController
{
    public function getOnline()
    {
        $atcSessions = Atc::online()->get();

        return $this->viewMake("networkdata::site.online")
                    ->with("atcSessions", $atcSessions);
    }
}
