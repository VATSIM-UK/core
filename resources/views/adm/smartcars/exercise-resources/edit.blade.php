@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">{{ $flight->name }} - Edit Resource</h3>
                </div>
                <div class="box-body">
                    <form method="POST"
                          action="{{ route('adm.smartcars.exercises.resources.update', [$flight, $resource]) }}"
                          enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('adm.smartcars.exercise-resources.includes.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
