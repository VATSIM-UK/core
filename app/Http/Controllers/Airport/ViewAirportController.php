<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\BaseController;
use App\Models\Airport;

class ViewAirportController extends BaseController
{
    public function index()
    {
        $airports = Airport::uk()->orderBy('name')->get()->split(2);

        return view('airport.index')->with('airports', $airports);
    }

    public function show(Airport $airport)
    {
        $airport->load(['navaids', 'runways', 'procedures', 'procedures.runway']);

        $stations = $airport->stations()->orderByDesc('type')->get()->groupBy('type')->transform(function ($group) {
            return $group->sortBy(function ($station){
                return strlen($station->callsign)*($station->sub_station ? 2 : 1);
            });
        })->collapse();

        return view('airport.view')->with('airport', $airport)->with('stations', $stations);
    }
}
