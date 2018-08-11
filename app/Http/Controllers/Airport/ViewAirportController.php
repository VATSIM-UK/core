<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\BaseController;
use App\Models\Airport;
use CobaltGrid\VatsimStandStatus\StandStatus;
use Illuminate\Support\Facades\File;

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
            return $group->sortBy(function ($station) {
                return strlen($station->callsign) * ($station->sub_station ? 2 : 1);
            });
        })->collapse();

        $stand_status = null;
        if (File::exists(resource_path().'/assets/data/stands/'.$airport->icao.'.csv')) {
            $stand_status = (new StandStatus($airport->icao, resource_path().'/assets/data/stands/'.$airport->icao.'.csv', $airport->latitude, $airport->longitude, false, null))->setMaxAircraftAltitude($airport->elevation + 300)->parseData();
        }

        return view('airport.view')->with(['airport' => $airport, 'stations' => $stations, 'stands' => $stand_status]);
    }
}
