<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Http\Controllers\Adm\AdmController as Controller;
use App\Models\Smartcars\Aircraft;
use Illuminate\Http\Request;

class AircraftController extends Controller
{
    protected $defaults = [
        'range_nm' => 0,
        'weight_kg' => 0,
        'cruise_altitude' => 0,
        'max_passengers' => 0,
        'max_cargo_kg' => 0,
    ];

    /**
     * Define where to redirect requests.
     *
     * @return string
     */
    public function redirectTo()
    {
        return route('adm.smartcars.aircraft.index');
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'icao' => 'required|string|max:4',
            'name' => 'required|string|max:12',
            'fullname' => 'required|string|max:50',
            'registration' => 'required|string|max:5',
            'range_nm' => 'nullable|numeric|max:1000000',
            'weight_kg' => 'nullable|numeric|max:1000000',
            'cruise_altitude' => 'nullable|numeric|max:1000000',
            'max_passengers' => 'nullable|numeric|max:1000000',
            'max_cargo_kg' => 'nullable|numeric|max:1000000',
        ];
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
        $this->authorize('use-permission', 'adm/smartcars/aircraft');

        $aircraft = Aircraft::orderBy('icao')->paginate(50);

        return $this->viewMake('adm.smartcars.aircraft')->with('aircraft', $aircraft);
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
        $this->authorize('use-permission', 'adm/smartcars/aircraft/create');

        return $this->viewMake('adm.smartcars.aircraft-form')->with('aircraft', new Aircraft());
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
        $this->authorize('use-permission', 'adm/smartcars/aircraft/create');

        $this->validate($request, $this->rules());

        Aircraft::create(array_filter($request->all()) + $this->defaults);

        return redirect($this->redirectPath())->with('success', 'Aircraft created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Aircraft $aircraft)
    {
        $this->authorize('use-permission', 'adm/smartcars/aircraft/update');

        return $this->viewMake('adm.smartcars.aircraft-form')->with('aircraft', $aircraft);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Aircraft $aircraft)
    {
        $this->authorize('use-permission', 'adm/smartcars/aircraft/update');

        $this->validate($request, $this->rules());

        $aircraft->fill(array_filter($request->all()) + $this->defaults)->save();

        return redirect($this->redirectPath())->with('success', 'Aircraft updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Aircraft $aircraft)
    {
        $this->authorize('use-permission', 'adm/smartcars/aircraft/delete');

        $aircraft->delete();

        return redirect($this->redirectPath())->with('success', 'Aircraft deleted.');
    }
}
