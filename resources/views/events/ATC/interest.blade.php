@extends('layout')

@section('content')
<div class="panel panel-ukblue">
    <div class="panel-heading">Add ATC Interest</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-7 col-md-offset-2">
                    {!! Form::open(["route" => ["events.atc.interest"]]) !!}
                    <p>
                        {{Form::label('events_current', 'What event do you want to show controlling interest in?')}}
                        {{Form::select('events_current', $currentEvents, [], ['class' => 'form-control']) }}
                    </p>
                    <p class="text-center">
                        <button type="submit" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
                    </p>
                    {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@stop