<?php

namespace App\Http\Controllers\Smartcars\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Smartcars\Aircraft;
use App\Models\Smartcars\Airport;
use App\Models\Smartcars\Bid;
use App\Models\Smartcars\Pirep;
use App\Models\Smartcars\Posrep;
use Input;

class Flight extends AdmController
{
    public function getSearch()
    {
        $flights = \App\Models\Smartcars\Flight::with('departure')->with('arrival')->where('enabled', '=', 1);

        $departure = Airport::findByIcao(Input::get('departureicao'));
        if (Input::get('departureicao', null) != null) {
            if (!$departure) {
                return 'NONE';
            }

            $flights->where('departure_id', '=', $departure->id);
        }

        $arrival = Airport::findByIcao(Input::get('arrivalicao'));
        if (Input::get('arrivalicao', null) != null) {
            if (!$arrival) {
                return 'NONE';
            }

            $flights->where('arrival_id', '=', $arrival->id);
        }

        if (Input::get('mintime', '') != '' && Input::get('maxtime', '') != '') {
            $flights->where('flight_time', '>=', Input::get('mintime'))
                ->where('flight_time', '<=', Input::get('maxtime'));
        }

        $flights = $flights->get();

        $return = '';
        foreach ($flights as $f) {
            $return .= $f->id.'|';
            $return .= $f->code.'|';
            $return .= $f->flightnum.'|';
            $return .= $f->departure->icao.'|';
            $return .= $f->arrival->icao.'|';
            $return .= $f->route.'|';
            $return .= $f->cruise_altitude.'|'; // Cruise altitude.
            $return .= $f->aircraft->id.'|';
            $return .= $f->flight_time.':00|';
            $return .= '00:00 GMT|'; // Departure time
            $return .= '23:59 GMT|'; // Arrival time
            $return .= '0123456;'; // Days of week.
        }

        return rtrim($return, ';');
    }

    public function getBids()
    {
        $bids = Bid::pending()->accountId(Input::get('dbid'))->get();

        if ($bids->count() == 0) {
            return 'NONE';
        }

        $return = '';
        foreach ($bids as $b) {
            $f = $b->flight;
            $return .= $b->id.'|';
            $return .= $f->id.'|';
            $return .= $f->code.'|';
            $return .= $f->flightnum.'|';
            $return .= $f->departure->icao.'|';
            $return .= $f->arrival->icao.'|';
            $return .= $f->route.'|';
            $return .= $f->cruise_altitude.'|'; // Cruise altitude.
            $return .= $f->aircraft->id.'|';
            $return .= $f->flight_time.':00|';
            $return .= '00:00 GMT|'; // Departure time
            $return .= '23:55 GMT|'; // Arrival time
            $return .= '0|'; // Load
            $return .= 'P|'; // Type (p=Pax,c=Cargo)
            $return .= '0123456;'; // Days of week.
        }

        return rtrim($return, ';');
    }

    public function postPosition()
    {
        $aircraft = Aircraft::findByRegistration(Input::get('aircraft'));

        $bid = Bid::find(Input::get('bidid'));
        $flight = $bid->flight;

        if ($flight->id != Input::get('routeid')) {
            return 'ERROR';
        }

        $posrep = new Posrep;
        $posrep->bid_id = $bid->id;
        $posrep->aircraft_id = $aircraft->id;
        $posrep->route = Input::get('route') ?: '';
        $posrep->altitude = Input::get('altitude');
        $posrep->heading_mag = Input::get('magneticheading');
        $posrep->heading_true = Input::get('trueheading');
        $posrep->latitude = str_replace(',', '.', Input::get('latitude'));
        $posrep->longitude = str_replace(',', '.', Input::get('longitude'));
        $posrep->groundspeed = Input::get('groundspeed');
        $posrep->distance_remaining = Input::get('distanceremaining');
        $posrep->phase = Input::get('phase');
        $posrep->time_departure = Input::get('departuretime');
        $posrep->time_remaining = Input::get('timeremaining') !== 'N/A' ? Input::get('timeremaining') : null;
        $posrep->time_arrival = Input::get('arrivaltime') !== 'N/A' ? Input::get('arrivaltime') : null;
        $posrep->network = Input::get('onlinenetwork');
        $posrep->save();

        return 'SUCCESS';
    }

    public function postReport()
    {
        $aircraft = Aircraft::find(Input::get('aircraft'));

        $bid = Bid::find(Input::get('bidid'));
        $flight = $bid->flight;

        if ($flight->id != Input::get('routeid')) {
            return 'ERROR';
        }

        $pirep = new Pirep;
        $pirep->aircraft_id = $aircraft->id;
        $pirep->bid_id = $bid->id;
        $pirep->route = Input::get('route') ?: '';
        $pirep->flight_time = str_replace('.', ':', Input::get('flighttime')).':00';
        $pirep->landing_rate = Input::get('landingrate');
        $pirep->comments = Input::get('comments');
        $pirep->fuel_used = Input::get('fuelused');
        $pirep->log = Input::get('log');
        $pirep->save();

        $bid->complete();

        return 'SUCCESS';
    }

    public function getBid()
    {
        $flight = \App\Models\Smartcars\Flight::find(Input::get('routeid'));

        if (!$flight) {
            return 'INVALID_ROUTEID';
        }

        $bidCheck = Bid::pending()->flightId($flight->id)->accountId(Input::get('dbid'))->count();

        if ($bidCheck > 0) {
            return 'FLIGHT_ALREADY_BID';
        }

        $bid = new Bid;
        $bid->flight_id = $flight->id;
        $bid->account_id = Input::get('dbid');
        $bid->save();

        return 'FLIGHT_BID';
    }

    public function getBidDelete()
    {
        $bid = \App\Models\Smartcars\Bid::find(Input::get('bidid'));

        if ($bid->account_id != Input::get('dbid')) {
            return 'AUTH_FAILED';
        }

        $bid->delete();

        return 'FLIGHT_DELETED';
    }
}
