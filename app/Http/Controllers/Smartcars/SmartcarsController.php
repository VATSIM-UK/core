<?php

namespace App\Http\Controllers\Smartcars;

use App\Http\Controllers\BaseController;
use App\Models\Smartcars\Bid;
use App\Models\Smartcars\Flight;
use App\Models\Smartcars\Pirep;
use Illuminate\Http\Request;

class SmartcarsController extends BaseController
{
    public function getDashboard()
    {
        $exercises = Flight::featured()->enabled()->orderBy('created_at')->get();
        $pireps = Pirep::query()->belongsTo($this->account->id)->count();

        return $this->viewMake('fte.dashboard')
            ->with('exercises', $exercises)
            ->with('pireps', $pireps);
    }

    public function getGuide()
    {
        return $this->viewMake('fte.guide');
    }

    public function getExercise(?Flight $exercise = null)
    {
        if (is_null($exercise)) {
            $exercises = Flight::enabled()->orderBy('created_at')->get();

            if ($exercises->count() == 0) {
                return redirect()->route('fte.dashboard')->withError('There are no exercises available at the moment.');
            }

            return $this->viewMake('fte.exercises')->with('exercises', $exercises);
        } else {
            $this->authorize('bid', $exercise);

            $bid = Bid::accountId($this->account->id)->flightId($exercise->id)->pending()->first();

            return $this->viewMake('fte.exercise')
                ->with('flight', $exercise)
                ->with('criteria', $exercise->criteria->sortBy('order'))
                ->with('booking', $bid);
        }
    }

    public function bookExercise(Flight $exercise)
    {
        $this->authorize('bid', $exercise);

        $bids = Bid::accountId($this->account->id)->flightId($exercise->id)->pending()->get();
        if ($bids->isNotEmpty()) {
            return redirect()->back()->with('error', 'Exercise has already been booked.');
        }

        Bid::create([
            'account_id' => $this->account->id,
            'flight_id' => $exercise->id,
        ]);
        $guideRoute = route('fte.guide');

        return redirect()->back()->with('success', 'Exercise booked successfully.<br>Make sure you have our flight tracking software, smartCARS, setup!<br>Unsure? <a href="'.$guideRoute.'">Click here</a> to get started.');
    }

    public function cancelExercise(Flight $exercise)
    {
        $bids = Bid::accountId($this->account->id)->flightId($exercise->id)->pending()->get();
        if ($bids->isEmpty()) {
            return redirect()->back()->with('error', 'There are no bookings to remove.');
        }

        $bids->each(function ($bid) {
            $bid->delete();
        });

        return redirect()->back()->with('success', 'Exercise booking successfully deleted.');
    }

    public function getHistory(Request $request, ?Pirep $pirep = null)
    {
        if (is_null($pirep)) {
            $pireps = Pirep::query()->belongsTo($request->user()->id)->orderByDesc('created_at')->get();

            return $this->viewMake('fte.history')->with('pireps', $pireps);
        } else {
            $this->authorize('viewAccount', $pirep);

            return $this->viewMake('fte.completed-flight')
                ->with('pirep', $pirep)
                ->with('bid', $pirep->bid)
                ->with('flight', $pirep->bid->flight)
                ->with('criteria', $pirep->bid->flight->criteria->sortBy('order'))
                ->with('posreps', $pirep->bid->posreps->sortBy('created_at'));
        }
    }
}
