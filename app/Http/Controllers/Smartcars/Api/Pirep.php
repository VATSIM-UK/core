<?php

namespace App\Http\Controllers\Smartcars\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Smartcars\Airport;
use App\Models\Smartcars\Pirep as PirepData;
use Illuminate\Support\Facades\Request;

class Pirep extends AdmController
{
    public function getSearch()
    {
        $pireps = PirepData::with('bid.flight')
            ->with('bid.flight.departure')
            ->with('bid.flight.arrival')
            ->with('bid.flight.aircraft')
            ->belongsTo(Request::input('dbid'));

        $departure = Airport::findByIcao(Request::input('departureicao'));
        if (Request::input('departureicao', null) != null) {
            if (! $departure) {
                return 'NONE';
            }

            $pireps->whereHas('bid.flight', function ($query) use ($departure) {
                $query->where('departure_id', $departure->id);
            });
        }

        $arrival = Airport::findByIcao(Request::input('arrivalicao'));
        if (Request::input('arrivalicao', null) != null) {
            if (! $arrival) {
                return 'NONE';
            }

            $pireps->whereHas('bid.flight', function ($query) use ($arrival) {
                $query->where('arrival_id', $arrival->id);
            });
        }

        $pireps = $pireps->get();

        if ($pireps->isEmpty()) {
            return 'NONE';
        }

        $return = '';
        foreach ($pireps as $p) {
            $return .= $p->id.'|';
            $return .= $p->bid->flight->code.'|';
            $return .= $p->bid->flight->flightnum.'|';
            $return .= $p->created_at->toDateString().'|';
            $return .= $p->bid->flight->departure->icao.'|';
            $return .= $p->bid->flight->arrival->icao.'|';
            $return .= $p->bid->flight->aircraft->id.';';
        }

        return rtrim($return, ';');
    }

    public function getData()
    {
        $pirep = PirepData::find(Request::input('pirepid'));

        $return = [];
        $return['duration'] = $pirep->flight_time;
        $return['landingrate'] = $pirep->landing_rate;
        $return['fuelused'] = $pirep->fuel_used;
        $return['status'] = 1;
        $return['log'] = str_replace(',', '', $pirep->log);

        return response()->csv($return);
    }
}
