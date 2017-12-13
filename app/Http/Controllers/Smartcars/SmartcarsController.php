<?php

namespace App\Http\Controllers\Smartcars;

use App\Http\Controllers\BaseController;
use App\Models\Smartcars\Bid;
use App\Models\Smartcars\Flight;

class SmartcarsController extends BaseController
{
    public function getDashboard()
    {
        $exercises = Flight::featured()->enabled()->orderBy('created_at')->get();

        return view('fte.dashboard')->with('exercises', $exercises);
    }

    public function getMap()
    {
        return view('fte.map');
    }

    public function getExercise(Flight $exercise = null)
    {
        if (is_null($exercise)) {
            $exercises = Flight::enabled()->orderBy('created_at')->get();

            return view('fte.exercises')->with('exercises', $exercises);
        } else {
            $bid = Bid::accountId($this->account->id)->flightId($exercise->id)->pending()->first();

            return view('fte.exercise')->with('exercise', $exercise)->with('booking', $bid);
        }
    }

    public function bookExercise(Flight $exercise)
    {
        $bids = Bid::accountId($this->account->id)->flightId($exercise->id)->pending()->get();
        if ($bids->isNotEmpty()) {
            return redirect()->back()->with('error', 'Exercise has already been booked.');
        }

        Bid::create([
            'account_id' => $this->account->id,
            'flight_id' => $exercise->id,
        ]);

        return redirect()->back()->with('success', 'Exercise booked successfully.');
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

    public function getHistory($flightId = null)
    {
        if (is_null($flightId)) {
            return view('fte.history');
        } else {
            return view('fte.completed-flight');
        }
    }
}
