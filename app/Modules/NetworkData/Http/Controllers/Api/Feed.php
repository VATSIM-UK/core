<?php

namespace App\Modules\Networkdata\Http\Controllers\Api;

use App\Modules\NetworkData\Models\Atc;
use Illuminate\Routing\Controller as BaseController;

class Feed extends BaseController
{
    public function getOnline()
    {
        $atcSessions = Atc::remember(2)
                          ->online()
                          ->onFrequency()
                          ->isUK()
                          ->get();

        return response()->json($atcSessions->toArray());
    }
}
