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
        return $this->viewMake('site.airport.view')
            ->with(
                [
                    'airport' => $airport,
                    'stands' => $this->ukcp->getStandStatus(Str::upper($airport->icao)),
                ]
            );
    }
}
