<?php

namespace App\Listeners\Smartcars;

use App\Events\Smartcars\BidCompleted;
use App\Models\NetworkData\Pilot as NetworkData;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateFlightCriteria implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  BidCompleted  $event
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
            $positionValid = false;
            $altitudeValid = false;
            $speedValid = false;

            foreach ($criteria as $criterion) {
                if ($posrep->positionIsValid($criterion)) {
                    $positionValid = true;

                    if ($posrep->altitudeIsValid($criterion)) {
                        $altitudeValid = true;

                        if ($posrep->speedIsValid($criterion)) {
                            $speedValid = true;
                        }
                    }
                }
            }

            if (! $positionValid) {
                $pirep->markFailed("Failed: You went off track at posrep #{$posrep->id}.", $posrep->id);
                $pirep->save();

                return;
            }

            if (! $altitudeValid) {
                $pirep->markFailed("Failed: You went outside of the altitude restriction at posrep #{$posrep->id}.", $posrep->id);
                $pirep->save();

                return;
            }

            if (! $speedValid) {
                $pirep->markFailed("Failed: You went outside of the speed restriction at posrep #{$posrep->id}.", $posrep->id);
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

        // Check disabled whilst pilot data is not recorded.
        // if (! $this->onNetwork($pirep, $posreps)) {
        //     $pirep->markFailed('Failed: You were not connected to the VATSIM network.');
        //     $pirep->save();

        //     return;
        // }

        $pirep->markPassed('Success: Flight passed all required checks');
        $pirep->save();
    }

    protected function onNetwork($pirep, $posreps)
    {
        $pirepTime = $this->minutes($pirep->flight_time);

        $networkTime = NetworkData::where('disconnected_at', '>', $posreps->first()->created_at)
            ->where('connected_at', '<', $posreps->last()->created_at)
            ->where('account_id', '=', $posreps->first()->bid->account->id)
            ->sum('minutes_online');

        if ($pirepTime < 0) {
            return false;
        }

        return (($networkTime / $pirepTime) * 100) < 90;
    }

    protected function minutes($time)
    {
        $time = explode(':', $time);

        return ($time[0] * 60) + $time[1] + ($time[2] / 60);
    }
}
