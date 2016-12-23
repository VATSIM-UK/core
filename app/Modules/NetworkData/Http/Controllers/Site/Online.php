<?php

namespace App\Modules\Networkdata\Http\Controllers\Site;

use App\Modules\NetworkData\Models\Atc;
use App\Http\Controllers\BaseController;

class Online extends BaseController
{
    public function getOnline()
    {
        $atcSessions = Atc::remember(2)
                          ->online()
                          ->isUK()
                          ->with([
                              'account' => function ($q) {
                                  $q->remember(1);
                              },
                          ])->get();

        return $this->viewMake('networkdata::site.online')
                    ->with('atcSessions', $atcSessions);
    }
}
