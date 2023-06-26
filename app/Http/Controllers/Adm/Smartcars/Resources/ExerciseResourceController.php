<?php

namespace App\Http\Controllers\Adm\Smartcars\Resources;

use App\Http\Controllers\Adm\AdmController as Controller;
use App\Models\Smartcars\Flight;
use App\Models\Smartcars\FlightResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseResourceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            $this->authorize('use-permission', 'adm/smartcars/exercises/update');

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Flight $exercise)
    {
        return $this->viewMake('adm.smartcars.exercise-resources.index')
            ->with('flight', $exercise)
            ->with('resources', $exercise->resources);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Flight $exercise)
    {
        return $this->viewMake('adm.smartcars.exercise-resources.create')
            ->with('flight', $exercise)
            ->with('resource', new FlightResource());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Flight $exercise)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'type' => 'required|string|in:file,uri',
            'file' => 'nullable|file|max:10240|mimes:pdf',
            'uri' => 'nullable|url|max:255',
        ]);

        $resource = new FlightResource($request->only('display_name', 'type'));
        $resource->flight()->associate($exercise);
        $resource->resource = $resource->type === 'file'
            ? $request->file('file')->store('smartcars/exercises/resources', ['disk' => 'public'])
            : $resource->resource = $request->input('uri');
        $resource->save();

        return redirect()->route('adm.smartcars.exercises.resources.index', $exercise)
            ->with('success', 'Resource added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Flight $exercise, FlightResource $resource)
    {
        return $this->viewMake('adm.smartcars.exercise-resources.edit')
            ->with('flight', $exercise)
            ->with('resource', $resource);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Flight $exercise, FlightResource $resource)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'file' => 'nullable|file|max:10240|mimes:pdf',
            'uri' => 'nullable|url|max:255',
        ]);

        if ($resource->type === 'file' && $request->file('file')) {
            Storage::drive('public')->delete($resource->resource);
            $resource->resource = $request->file('file')->store('smartcars/exercises/resources', ['disk' => 'public']);
        } elseif ($resource->type === 'uri' && $request->input('uri')) {
            $resource->resource = $request->input('uri');
        }

        $resource->save();

        return redirect()->route('adm.smartcars.exercises.resources.index', $exercise)
            ->with('success', 'Resource edited successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Flight $exercise, FlightResource $resource)
    {
        if ($resource->type === 'file') {
            Storage::drive('public')->delete($resource->resource);
        }

        $resource->delete();

        return redirect()->route('adm.smartcars.exercises.resources.index', $exercise)
            ->with('success', 'Resource deleted successfully.');
    }
}
