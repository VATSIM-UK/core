<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Http\Controllers\Adm\AdmController as Controller;
use App\Models\Smartcars\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    /**
     * Get the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'icao' => 'required|string|max:4|unique:smartcars_airport,icao',
            'name' => 'required|string|max:100',
            'country' => 'required|string|max:50',
            'latitude' => 'required|numeric|min:-90|max:90',
            'longitude' => 'required|numeric|min:-180|max:180',
        ];
    }

    /**
     * Define where to redirect requests.
     *
     * @return string
     */
    public function redirectTo()
    {
        return route('adm.smartcars.airports.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('use-permission', 'adm/smartcars/airports');

        $airports = Airport::query()->orderBy('icao')->paginate(50);

        return $this->viewMake('adm.smartcars.airports')->with('airports', $airports);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('use-permission', 'adm/smartcars/airports/create');

        return $this->viewMake('adm.smartcars.airport-form')->with('airport', new Airport());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('use-permission', 'adm/smartcars/airports/create');

        $this->validate($request, $this->rules());

        Airport::create(array_filter($request->all()));

        return redirect($this->redirectPath())->with('success', 'Airport created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Airport $airport)
    {
        $this->authorize('use-permission', 'adm/smartcars/airports/update');

        return $this->viewMake('adm.smartcars.airport-form')->with('airport', $airport);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Airport $airport)
    {
        $this->authorize('use-permission', 'adm/smartcars/airports/update');

        $rules = $this->rules();
        $rules['icao'] .= ",{$airport->id}";
        $this->validate($request, $rules);

        $airport->fill(array_filter($request->all()))->save();

        return redirect($this->redirectPath())->with('success', 'Airport updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Airport $airport)
    {
        $this->authorize('use-permission', 'adm/smartcars/airports/delete');

        $airport->delete();

        return redirect($this->redirectPath())->with('success', 'Airport deleted.');
    }
}
