<?php

namespace App\Http\Controllers\Adm\Smartcars;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Smartcars\Aircraft;
use App\Models\Smartcars\Airport;

class SmartcarsController extends AdmController
{
    public function getAircraft()
    {
        $this->authorize('use-permission', 'smartcars/aircraft');

        $aircraft = Aircraft::query()->orderBy('icao')->paginate(50);

        return $this->viewMake('adm.smartcars.aircraft')->with('aircraft', $aircraft);
    }

    public function getAirports()
    {
        $this->authorize('use-permission', 'smartcars/airports');

        $airports = Airport::query()->orderBy('icao')->paginate(50);

        return $this->viewMake('adm.smartcars.airports')->with('airports', $airports);
    }

    public function getExercises()
    {
        $this->authorize('use-permission', 'smartcars/exercises');

        return $this->viewMake('adm.smartcars.exercises');
    }

    public function getFlights()
    {
        $this->authorize('use-permission', 'smartcars/flights');

        return $this->viewMake('adm.smartcars.flights');
    }
}
