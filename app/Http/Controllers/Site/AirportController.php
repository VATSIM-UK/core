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
        $file_path = resource_path().'/assets/data/stands/'.strtolower($airport->icao).'.csv';

        if (File::exists($file_path)) {
            $standStatus = new StandStatus(
                $airport->latitude,
                $airport->longitude,
                StandStatus::COORD_FORMAT_CAA
            );

            return $standStatus
                ->loadStandDataFromCSV($file_path)
                ->setMaxAircraftAltitude($airport->elevation + 300)
                ->parseData();
        }
    }
}
