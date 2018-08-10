<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\BaseController;
    use App\Models\Airport;

    class ViewAirportController extends BaseController
    {
        public function show(Airport $airport)
        {
            $airport->load(['navaids', 'runways', 'procedures', 'stations']);

            return view('airport.view')->with('airport', $airport);
        }
    }
