<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Models\Smartcars\Pirep;
use App\Http\Controllers\Adm\AdmController as Controller;

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

        $flights = Pirep::query()->orderBy('created_at')->paginate(50);

        return $this->viewMake('adm.smartcars.flights')->with('flights', $flights);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Smartcars\Pirep  $pirep
     * @return \Illuminate\Http\Response
     */
    public function show(Pirep $pirep)
    {
        //
    }
}
