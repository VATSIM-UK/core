<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

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
                    $pirep->markFailed("Failed: You went off track at posrep #{$posrep->id}.", $posrep->id);
                    $pirep->save();

                    return;
                }

                if (!$posrep->isAltitudeValid($criterion)) {
                    $pirep->markFailed("Failed: You went outside of the altitude restriction at posrep #{$posrep->id}.", $posrep->id);
                    $pirep->save();

                    return;
                }

                if (!$posrep->isSpeedValid($criterion)) {
                    $pirep->markFailed("Failed: You went outside of the speed restriction at posrep #{$posrep->id}.", $posrep->id);
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

        $pirepTime = $this->minutes($pirep->flight_time);

        $networkTime = DB::table('networkdata_pilots')
            ->whereBetween('connected_at', [$posreps->first()->created_at, $posreps->last()->created_at])
            ->where('account_id', '=', $bid->account_id)
            ->sum('minutes_online');

        if ((($networkTime / $pirepTime) * 100 ) < 90) {
            $pirep->markFailed('You were not connected to the VATSIM network.', null);
            $pirep->save();

            return;
        }

        $pirep->markPassed('Success: Flight passed all required checks');
        $pirep->save();
    }

    public function minutes($time)
    {
        $time = explode(':', $time);
        return ($time[0]*60) + ($time[1]) + ($time[2]/60);
    }
}
