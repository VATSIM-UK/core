<?php

namespace App\Http\Controllers\Adm\Smartcars;

use App\Http\Controllers\Adm\AdmController;

class SmartcarsController extends AdmController
{
    public function getAircraft()
    {
        return $this->viewMake('adm.smartcars.aircraft');
    }

    public function getAirports()
    {
        return $this->viewMake('adm.smartcars.airports');
    }

    public function getExercises()
    {
        return $this->viewMake('adm.smartcars.exercises');
    }

    public function getFlights()
    {
        return $this->viewMake('adm.smartcars.flights');
    }
}
