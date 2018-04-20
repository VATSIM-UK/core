<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
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

        foreach ($posreps as $posrep) {

            foreach ($criteria as $criterion) {

                if (!$posrep->isPositionValid($criterion)) {
                    $pirep->markFailed("Failed: You went off track at posrep #{$posrep->id}.");
                    $pirep->save();

                    return;
                }

                if (!$posrep->isAltitudeValid($criterion)) {
                    $pirep->markFailed("Failed: You went outside of the altitude restriction at posrep #{$posrep->id}.");
                    $pirep->save();

                    return;
                }

                if (!$posrep->isSpeedValid($criterion)) {
                    $pirep->markFailed("Failed: You went outside of the speed restriction at posrep #{$posrep->id}.");
                    $pirep->save();

                    return;
                }
            }

        }

        if (str_contains($pirep->log, 'Simulation rate set to')) {
            // simrate was changed
            $pirep->markFailed('Failed: The simrate was changed during the flight');
            $pirep->save();

            return;
        }

        $pirep->markPassed('Success: Flight passed all required checks');
        $pirep->save();
    }
}
