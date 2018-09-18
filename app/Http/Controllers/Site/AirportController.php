<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\BaseController;
use App\Models\Airport;
use CobaltGrid\VatsimStandStatus\StandStatus;
use Illuminate\Support\Facades\File;

class AirportController extends BaseController
{
    public function index()
    {
        $airports = Airport::uk()->orderBy('name')->get()->split(2);

        return $this->viewMake('site.airport.index')->with('airports', $airports);
    }

    public function show(Airport $airport)
    {
        $airport->load(['navaids', 'runways', 'procedures', 'procedures.runway']);

        $stations = $airport->stations()->orderByDesc('type')->get()->groupBy('type')->transform(function ($group) {
            return $group->sortBy(function ($station) {
                return strlen($station->callsign) * ($station->sub_station ? 2 : 1);
            });
        })->collapse();

        return $this->viewMake('site.airport.view')->with(['airport' => $airport, 'stations' => $stations, 'stands' => $this->loadStandStatus($airport)]);
    }

    private function loadStandStatus($airport)
    {
        if (File::exists(resource_path().'/assets/data/stands/'.strtolower($airport->icao).'.csv')) {
            return (new StandStatus($airport->icao, resource_path().'/assets/data/stands/'.$airport->icao.'.csv', $airport->latitude, $airport->longitude, false, null))->setMaxAircraftAltitude($airport->elevation + 300)->parseData();
        }
    }
}
