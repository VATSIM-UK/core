<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
use App\Models\Smartcars\FlightCriterion;
use App\Models\Smartcars\Posrep;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateFlightCriteria implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param BidCompleted $event
     * @return void
     */
    public function handle(BidCompleted $event)
    {
        $bid = $event->bid;
        $flight = $bid->flight;
        $criteria = $flight->criteria->sortBy('order');
        $pirep = $bid->pirep;
        $posreps = $bid->posreps->sortBy('created_at');

        $newCriterion = true;
        $criterion = $criteria->shift();
        foreach ($posreps as $posrep) {
            if ($this->validPosrep($posrep, $criterion)) {
                $newCriterion = false;

                continue;
            }

            if ($newCriterion) {
                // went outside of the defined criteria
                $pirep->markFailed("Posrep #{$posrep->id} failed at criterion #{$criterion->id}");
                $pirep->save();

                return;
            }

            $newCriterion = true;
            $criterion = $criteria->shift();
            if ($criterion === null) {
                // flight did not finish within the defined criteria
                $pirep->markFailed("Posrep #{$posrep->id} failed - eligible posrep after all criteria fulfilled");
                $pirep->save();

                return;
            }
        }

        if (($criterion = $criteria->shift())) {
            // flight finished early, before all criteria were fulfilled
            $pirep->markFailed("Not all criteria were fulfilled, starting from criterion #{$criterion->id}");
            $pirep->save();

            return;
        }

        $pirep->markPassed('All posreps passed successfully and all criteria were met');
        $pirep->save();
    }

    /**
     * @param Posrep $posrep
     * @param FlightCriterion $criterion
     * @return bool
     */
    protected function validPosrep($posrep, $criterion)
    {
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
