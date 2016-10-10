<?php namespace App\Modules\Smartcars\Http\Controllers\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Smartcars\Models\Aircraft;
use App\Modules\Smartcars\Models\Airport;
use App\Modules\Smartcars\Models\Bid;
use App\Modules\Smartcars\Models\Pirep as PirepData;
use App\Modules\Smartcars\Models\Posrep;
use App\Modules\Smartcars\Models\Session;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Auth;
use Cache;
use Input;
use Request;

class Pirep extends AdmController
{
    public function getSearch()
    {
        $pireps = PirepData::with("bid.flight")
                           ->with("bid.flight.departure")
                           ->with("bid.flight.arrival")
                           ->with("bid.flight.aircraft")
                           ->belongsTo(Input::get("dbid"));

        $departure = Airport::findByIcao(Input::get("departureicao"));
        if (Input::get("departureicao", null) != null) {
            if (!$departure) {
                return "NONE";
            }

            $pireps->where("departure_id", "=", $departure->id);
        }

        $arrival = Airport::findByIcao(Input::get("arrivalicao"));
        if (Input::get("arrivalicao", null) != null) {
            if (!$arrival) {
                return "NONE";
            }

            $pireps->where("arrival_id", "=", $arrival->id);
        }

        $pireps = $pireps->get();

        $return = "";
        foreach ($pireps as $p) {
            $return .= $p->id . "|";
            $return .= $p->bid->flight->code . "|";
            $return .= $p->bid->flight->flightnum . "|";
            $return .= $p->created_at->toDateString() . "|";
            $return .= $p->bid->flight->departure->icao . "|";
            $return .= $p->bid->flight->arrival->icao . "|";
            $return .= $p->bid->flight->aircraft->id . ";";
        }

        return rtrim($return, ";");
    }

    public function getData()
    {
        $pirep = PirepData::find(Input::get("pirepid"));

        $return = [];
        $return["duration"] = $pirep->flight_time;
        $return["landingrate"] = $pirep->landing_rate;
        $return["fuelused"] = $pirep->fuel_used;
        $return["status"] = 1;
        $return["log"] = str_replace(",", "", $pirep->log);

        return response()->csv($return);
    }
}

