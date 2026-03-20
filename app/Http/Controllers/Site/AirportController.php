<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\BaseController;
use App\Models\Airport;
use App\Services\Site\AirportService;

class AirportController extends BaseController
{
    public function __construct(private AirportService $airportService)
    {
        parent::__construct();
    }

    public function index()
    {
        $this->setTitle('Airports');

        return $this->viewMake('site.airport.index')->with('airports', $this->airportService->getAirportIndex());
    }

    public function show(Airport $airport)
    {
        $this->setTitle("{$airport->icao} | {$airport->name}");

        return $this->viewMake('site.airport.view')
            ->with(
                [
                    'airport' => $airport,
                    'stands' => $this->airportService->getStandStatus($airport),
                ]
            );
    }
}
