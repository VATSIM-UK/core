<?php

namespace App\Http\Controllers\Smartcars;

use App\Http\Controllers\BaseController;

class SmartcarsController extends BaseController
{
    public function getDashboard()
    {
        return view('fte.dashboard');
    }

    public function getMap()
    {
        return view('fte.map');
    }

    public function getExercise($exerciseId = null)
    {
        if (is_null($exerciseId)) {
            return view('fte.exercises');
        } else {
            return view('fte.exercise');
        }
    }

    public function getHistory($flightId = null)
    {
        if (is_null($flightId)) {
            return view('fte.history');
        } else {
            return view('fte.completed-flight');
        }
    }
}
