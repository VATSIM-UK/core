@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">{{ $flight->name }} - Edit Resource</h3>
                </div>
                <div class="box-body">
                    {!! Form::open(['method'  => 'put', 'route' => ['adm.smartcars.exercises.resources.update', $flight, $resource], 'files' => true]) !!}
                    @include('adm.smartcars.exercise-resources.includes.form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
