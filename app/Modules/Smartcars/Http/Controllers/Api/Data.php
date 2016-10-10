<?php namespace App\Modules\Smartcars\Http\Controllers\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Smartcars\Models\Aircraft;
use App\Modules\Smartcars\Models\Airport;
use App\Modules\Smartcars\Models\Pirep;
use App\Modules\Smartcars\Models\Session;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Auth;
use Cache;
use Input;
use Request;

class Data extends AdmController
{
    public function getPilotInfo(){
        $totalHours = Account::find(Input::get("dbid"))->pireps()->sum("flight_time");
        $totalFlights = Account::find(Input::get("dbid"))->pireps()->count();
        $averageLandingRate = (int) Account::find(Input::get("dbid"))->pireps()->avg("landing_rate");
        $totalPireps = $totalFlights;

        return $totalHours.",".$totalFlights.",".$averageLandingRate.",".$totalPireps;
    }

    public function getAirports(){
        $airports = Airport::all();

        $return = "";

        foreach($airports as $a){
            $return .= $a->id . "|";
            $return .= $a->icao . "|";
            $return .= $a->name . "|";
            $return .= $a->latitude . "|";
            $return .= $a->longitude . "|";
            $return .= $a->country . ";";
        }

        return rtrim($return, ";");
    }
    public function getAircraft(){
        $aircraft = Aircraft::all();

        $return = "";

        foreach($aircraft as $a){
            $return .= $a->id . ",";
            $return .= $a->fullname . ",";
            $return .= $a->icao . ",";
            $return .= $a->registration . ",";
            $return .= $a->max_passengers . ",";
            $return .= $a->max_cargo_kg . ",";
            $return .= "1;";
        }

        return rtrim($return, ";");
    }
}

