<?php

namespace App\Http\Controllers\NetworkData;

use App\Models\NetworkData\Atc;
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

        return $this->viewMake('network-data.site.online')
                    ->with('atcSessions', $atcSessions);
    }
}
