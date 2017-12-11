<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateFlightCriteria implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(BidCompleted $event)
    {
        $bid = $event->bid;
        $flight = $bid->flight;
        $flightCriteria = $flight->criteria->sortBy('order');
        $pirep = $bid->pirep;

        foreach ($flightCriteria as $criterion) {
            $posreps = $bid->posreps->filter(function ($posrep) use ($criterion) {
                if ($criterion->min_latitude !== null && $criterion->min_latitude > $posrep->latitude) {
                    return false;
                }

                if ($criterion->max_latitude !== null && $criterion->max_latitude < $posrep->latitude) {
                    return false;
                }

                if ($criterion->min_longitude !== null && $criterion->min_longitude > $posrep->longitude) {
                    return false;
                }

                if ($criterion->max_longitude !== null && $criterion->max_longitude < $posrep->longitude) {
                    return false;
                }

                if ($criterion->min_altitude !== null && $criterion->min_altitude > $posrep->altitude) {
                    return false;
                }

                if ($criterion->max_altitude !== null && $criterion->max_altitude < $posrep->altitude) {
                    return false;
                }

                if ($criterion->min_groundspeed !== null && $criterion->min_groundspeed > $posrep->groundspeed) {
                    return false;
                }

                if ($criterion->max_groundspeed !== null && $criterion->max_groundspeed > $posrep->groundspeed) {
                    return false;
                }

                return true;
            });

            if ($posreps->isEmpty()) {
                $pirep->markFailed("Failed on criterion #{$criterion->id}");
                $pirep->save();

                return;
            }
        }

        $pirep->markPassed();
        $pirep->save();
    }
}
