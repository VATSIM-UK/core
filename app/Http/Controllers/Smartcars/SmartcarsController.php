<?php

namespace App\Http\Controllers\Smartcars;

use App\Http\Controllers\BaseController;

class SmartcarsController extends BaseController
{
    public function getDashboard()
    {
        return view('fte.dashboard');
    }

    public function getHistory($flightId = null)
    {
        if (is_null($flightId)) {
            return view('fte.history');
        } else {
            return view('fte.completed-flight');
        }
    }

    public function getExercise()
    {
        return view('fte.exercise');
    }

    public function getMap()
    {
        return view('fte.map');
    }
}
