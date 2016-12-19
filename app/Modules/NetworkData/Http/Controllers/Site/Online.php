<?php

namespace App\Modules\Networkdata\Http\Controllers\Site;

use App\Modules\NetworkData\Models\Atc;
use App\Http\Controllers\BaseController;

class Online extends BaseController
{
    public function getOnline()
    {
        $atcSessions = Atc::online()->get();

        return $this->viewMake('networkdata::site.online')
                    ->with('atcSessions', $atcSessions);
    }
}
