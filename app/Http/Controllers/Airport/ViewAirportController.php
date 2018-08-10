<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\BaseController;
use App\Models\Airport;
use Illuminate\Support\Facades\DB;

class ViewAirportController extends BaseController
{
    public function index()
    {
        $airports = Airport::uk()->orderBy('name')->get()->split(2);

        return view('airport.index')->with('airports', $airports);
    }

    public function show(Airport $airport)
    {
        $airport->load(['navaids', 'runways', 'procedures', 'stations']);

        return view('airport.view')->with('airport', $airport);
    }
}
