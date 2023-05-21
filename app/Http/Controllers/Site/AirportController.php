<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\BaseController;
use App\Libraries\UKCP;
use App\Models\Airport;
use Illuminate\Support\Str;

class AirportController extends BaseController
{
    private readonly UKCP $ukcp;

    public function __construct(UKCP $ukcp)
    {
        $this->ukcp = $ukcp;
    }

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

        return $this->viewMake('site.airport.view')
        ->with(
            [
                'airport' => $airport,
                'stations' => $stations,
                'stands' => $this->ukcp->getStandStatus(Str::upper($airport->icao))
            ]
        );
    }
}
