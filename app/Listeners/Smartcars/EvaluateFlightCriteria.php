<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
use App\Models\Smartcars\FlightCriterion;
use App\Models\Smartcars\Posrep;
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
        $criteria = $flight->criteria->sortBy('order');
        $pirep = $bid->pirep;
        $posreps = $bid->posreps->sortBy('created_at');

        $criterion = $criteria->shift();
        foreach ($posreps as $posrep) {
            if ($this->validPosrep($posrep, $criterion)) {
                continue;
            }

            $criterion = $criteria->shift();
            if ($criterion === null) {
                $pirep->markFailed("Posrep #{$posrep->id} failed - eligible posrep after all criteria fulfilled");
                $pirep->save();
            }

            if ($this->validPosrep($posrep, $criterion)) {
                continue;
            }

            $pirep->markFailed("Posrep #{$posrep->id} failed at criterion #{$criterion->id}");
            $pirep->save();

            return;
        }

        $pirep->markPassed();
        $pirep->save();
    }

    /**
     * @param Posrep $posrep
     * @param FlightCriterion|null $criterion
     * @return bool
     */
    protected function validPosrep($posrep, $criterion)
    {
        if ($criterion === null) {
            return false;
        }

        // location
        if (!$criterion->hasPoint($posrep->latitude, $posrep->longitude)) {
            return false;
        }

        // altitude
        if ($criterion->min_altitude !== null && $posrep->altitude < $criterion->min_altitude) {
            return false;
        }

        if ($criterion->max_altitude !== null && $posrep->altitude > $criterion->max_altitude) {
            return false;
        }

        // groundspeed
        if ($criterion->min_groundspeed !== null && $posrep->groundspeed < $criterion->min_groundspeed) {
            return false;
        }

        if ($criterion->max_groundspeed !== null && $posrep->groundspeed > $criterion->max_groundspeed) {
            return false;
        }

        return true;
    }
}
