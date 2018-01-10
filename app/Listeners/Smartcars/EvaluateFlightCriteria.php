<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
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

        foreach ($posreps as $posrep) {
            $matchesCriteria = false;
            foreach ($criteria as $criterion) {
                if ($posrep->isValid($criterion)) {
                    $matchesCriteria = true;

                    break;
                }
            }

            if (!$matchesCriteria) {
                // posrep didn't match any criteria
                $pirep->markFailed("Failed: Posrep #{$posrep->id} didn't match any of the available criteria");
                $pirep->save();

                return;
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
