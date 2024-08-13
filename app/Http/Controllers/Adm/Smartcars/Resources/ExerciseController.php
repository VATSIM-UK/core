<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Http\Controllers\Adm\AdmController as Controller;
use App\Libraries\Storage\CoreUploadedFile;
use App\Models\Smartcars\Flight;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    /**
     * Define where to redirect requests.
     *
     * @return string
     */
    public function redirectTo()
    {
        return route('adm.smartcars.exercises.index');
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'image' => 'file|nullable|max:10240|mimes:jpeg,bmp,png',
            'code' => 'required|string|max:3',
            'name' => 'required|string|max:250',
            'description' => 'required|string|max:3000',
            'featured' => '',
            'flightnum' => 'required|string|max:10',
            'departure_id' => 'required|exists:smartcars_airport,id',
            'arrival_id' => 'required|exists:smartcars_airport,id',
            'route' => 'required|string|max:3000',
            'route_details' => 'required|string|max:3000',
            'aircraft_id' => 'required|exists:smartcars_aircraft,id',
            'cruise_altitude' => 'required|numeric|max:1000000',
            'distance' => 'required|numeric|max:1000000',
            'flight_time' => 'required|numeric|max:100',
            'notes' => 'required|string|max:3000',
            'enabled' => '',
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
        $this->authorize('use-permission', 'adm/smartcars/exercises');

        $exercises = Flight::orderBy('created_at')->with('departure', 'arrival', 'aircraft')->paginate(50);

        return $this->viewMake('adm.smartcars.exercises')->with('exercises', $exercises);
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
        $this->authorize('use-permission', 'adm/smartcars/exercises/create');

        return $this->viewMake('adm.smartcars.exercise-form')->with('exercise', new Flight);
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
        $this->authorize('use-permission', 'adm/smartcars/exercises/create');

        $this->validate($request, $this->rules());

        $exercise = new Flight;
        $exercise->fill(array_filter($request->except('image')));
        $exercise->featured = $request->input('featured') ? true : false;
        $exercise->enabled = $request->input('enabled') ? true : false;

        if ($request->hasFile('image')) {
            $file = new CoreUploadedFile($request->file('image'));
            $exercise->image()->store($file);
            $exercise->image = $file->getPathFileName();
        }
        $exercise->save();

        return redirect($this->redirectPath())->with('success', 'Exercise created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Flight $exercise)
    {
        $this->authorize('use-permission', 'adm/smartcars/exercises/update');

        return $this->viewMake('adm.smartcars.exercise-form')->with('exercise', $exercise);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Flight $exercise)
    {
        $this->authorize('use-permission', 'adm/smartcars/exercises/update');

        $this->validate($request, $this->rules());

        $exercise->fill(array_filter($request->except('image')));
        $exercise->featured = $request->input('featured') ? true : false;
        $exercise->enabled = $request->input('enabled') ? true : false;

        if ($request->hasFile('image')) {
            $file = new CoreUploadedFile($request->file('image'));
            $exercise->image()->store($file);
            $exercise->image = $file->getPathFileName();
        }
        $exercise->save();

        return redirect($this->redirectPath())->with('success', 'Exercise updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Flight $exercise)
    {
        $this->authorize('use-permission', 'adm/smartcars/exercises/delete');

        if ($exercise->image) {
            $exercise->image = null;
        }

        $exercise->delete();

        return redirect($this->redirectPath())->with('success', 'Exercise deleted.');
    }
}
