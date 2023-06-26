<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Http\Controllers\Adm\AdmController as Controller;
use App\Models\Smartcars\Pirep;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('use-permission', 'adm/smartcars/flights');

        $flights = Pirep::query()->orderByDesc('created_at')->paginate(50);

        return $this->viewMake('adm.smartcars.flights')->with('flights', $flights);
    }

    /**
     * Edit the specified resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Pirep $flight)
    {
        $this->authorize('use-permission', 'adm/smartcars/flights/override');

        return $this->viewMake('adm.smartcars.flight-form')
            ->with('pirep', $flight)
            ->with('bid', $flight->bid)
            ->with('flight', $flight->bid->flight)
            ->with('criteria', $flight->bid->flight->criteria->sortBy('order'))
            ->with('posreps', $flight->bid->posreps->sortBy('created_at'));
    }

    /**
     * Update the specified resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Pirep $flight)
    {
        $this->authorize('use-permission', 'adm/smartcars/flights/override');

        $this->validate($request, [
            'passed' => 'required|boolean',
            'reason' => 'required|string|max:250',
        ]);

        $flight->mark($request->input('passed'), $request->input('reason'), null);
        $flight->save();

        return redirect(route('adm.smartcars.flights.index'))->with('success', 'Flight updated.');
    }
}
