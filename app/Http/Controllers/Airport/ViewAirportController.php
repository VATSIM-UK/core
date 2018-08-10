<?php

    namespace App\Http\Controllers\Airport;

    use App\Models\Airport;
    use Illuminate\Http\Request;
    use App\Http\Controllers\BaseController;

    class ViewAirportController extends BaseController
    {
        public function show(Airport $airport)
        {
            $airport->load(['navaids','runways','procedures','stations']);
            return view('airport.view')->with('airport', $airport);
        }

    }
