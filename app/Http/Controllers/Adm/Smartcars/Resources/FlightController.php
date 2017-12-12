<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Models\Smartcars\Pirep;
use App\Http\Controllers\Adm\AdmController as Controller;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('use-permission', 'smartcars/flights');

        $flights = Pirep::query()->orderByDesc('created_at')->paginate(50);

        return $this->viewMake('adm.smartcars.flights')->with('flights', $flights);
    }

    /**
     * Edit the specified resource.
     *
     * @param  \App\Models\Smartcars\Pirep  $flight
     * @return \Illuminate\Http\Response
     */
    public function edit(Pirep $flight)
    {
        $this->authorize('use-permission', 'smartcars/flights/override');

        return $this->viewMake('adm.smartcars.flight-form')->with('flight', $flight);
    }

    /**
     * Update the specified resource.
     *
     * @param  \App\Models\Smartcars\Pirep  $flight
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pirep $flight)
    {
        $this->authorize('use-permission', 'smartcars/flights/override');

        $this->validate($request, [
            'passed' => 'required|boolean',
            'reason' => 'required|string|max:250',
        ]);

        if ($request->input('passed')) {
            $flight->markPassed($request->input('reason'));
        } else {
            $flight->markFailed($request->input('reason'));
        }

        return redirect(route('adm.smartcars.flights.index'))->with('success', 'Flight updated.');
    }
}
