<?php

namespace App\Http\Controllers\Smartcars\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Smartcars\Aircraft;
use App\Models\Smartcars\Airport;
use App\Models\Smartcars\Bid;
use App\Models\Smartcars\Pirep;
use App\Models\Smartcars\Posrep;
use Illuminate\Support\Facades\Request;

class Flight extends AdmController
{
    public function getSearch()
    {
        $flights = \App\Models\Smartcars\Flight::with('departure')->with('arrival')->where('enabled', '=', 1);

        $departure = Airport::findByIcao(Request::input('departureicao'));
        if (Request::input('departureicao', null) != null) {
            if (! $departure) {
                return 'NONE';
            }

            $flights->where('departure_id', '=', $departure->id);
        }

        $arrival = Airport::findByIcao(Request::input('arrivalicao'));
        if (Request::input('arrivalicao', null) != null) {
            if (! $arrival) {
                return 'NONE';
            }

            $flights->where('arrival_id', '=', $arrival->id);
        }

        if (Request::input('mintime', '') != '' && Request::input('maxtime', '') != '') {
            $flights->where('flight_time', '>=', Request::input('mintime'))
                ->where('flight_time', '<=', Request::input('maxtime'));
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
        $bids = Bid::pending()->accountId(Request::input('dbid'))->get();

        if (! $bids || $bids->count() == 0) {
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
        $aircraft = Aircraft::findByRegistration(Request::input('aircraft'));

        $bid = Bid::find(Request::input('bidid'));

        // Check bid has flight
        if (! $bid) {
            return 'ERROR';
        }

        $flight = $bid->flight;

        if ($flight->id != Request::input('routeid')) {
            return 'ERROR';
        }

        $posrep = new Posrep;
        $posrep->bid_id = $bid->id;
        $posrep->aircraft_id = $aircraft->id;
        $posrep->route = Request::input('route') ?: '';
        $posrep->altitude = Request::input('altitude');
        $posrep->heading_mag = Request::input('magneticheading');
        $posrep->heading_true = Request::input('trueheading');
        $posrep->latitude = str_replace(',', '.', Request::input('latitude'));
        $posrep->longitude = str_replace(',', '.', Request::input('longitude'));
        $posrep->groundspeed = Request::input('groundspeed');
        $posrep->distance_remaining = Request::input('distanceremaining');
        $posrep->phase = Request::input('phase');
        $posrep->time_departure = Request::input('departuretime');
        $posrep->time_remaining = Request::input('timeremaining') !== 'N/A' ? Request::input('timeremaining') : null;
        $posrep->time_arrival = Request::input('arrivaltime') !== 'N/A' ? Request::input('arrivaltime') : null;
        $posrep->network = Request::input('onlinenetwork');
        $posrep->save();

        return 'SUCCESS';
    }

    public function postReport()
    {
        $aircraft = Aircraft::find(Request::input('aircraft'));

        $bid = Bid::find(Request::input('bidid'));

        // Check bid has flight
        if (! $bid) {
            return 'ERROR';
        }

        $flight = $bid->flight;

        if ($flight->id != Request::input('routeid')) {
            return 'ERROR';
        }

        $pirep = new Pirep;
        $pirep->aircraft_id = $aircraft->id;
        $pirep->bid_id = $bid->id;
        $pirep->route = Request::input('route') ?: '';
        $pirep->flight_time = str_replace('.', ':', Request::input('flighttime')).':00';
        $pirep->landing_rate = Request::input('landingrate');
        $pirep->comments = Request::input('comments');
        $pirep->fuel_used = Request::input('fuelused');
        $pirep->log = Request::input('log');
        $pirep->save();

        $bid->complete();

        return 'SUCCESS';
    }

    public function getBid()
    {
        $flight = \App\Models\Smartcars\Flight::find(Request::input('routeid'));

        if (! $flight) {
            return 'INVALID_ROUTEID';
        }

        $bidCheck = Bid::pending()->flightId($flight->id)->accountId(Request::input('dbid'))->count();

        if ($bidCheck > 0) {
            return 'FLIGHT_ALREADY_BID';
        }

        $bid = new Bid;
        $bid->flight_id = $flight->id;
        $bid->account_id = Request::input('dbid');
        $bid->save();

        return 'FLIGHT_BID';
    }

    public function getBidDelete()
    {
        $bid = \App\Models\Smartcars\Bid::find(Request::input('bidid'));

        if ($bid->account_id != Request::input('dbid')) {
            return 'AUTH_FAILED';
        }

        $bid->delete();

        return 'FLIGHT_DELETED';
    }
}
