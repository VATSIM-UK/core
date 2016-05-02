@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Choose your Facility", ["type" => "fa", "key" => "question"]) !!}
            {!! Form::horizontal(["route" => ["visiting.application.statement.post"], "method" => "POST"]) !!}
            <div class="row">
                <div class="col-md-6 col-md-offset-3">

                    <p>
                        Please justify your application to the facility ({{ $application->facility->name }}) in the space provided below.
                        Remember to explain what your motivation is for applying to {{ $application->is_visit ? "visit" : "transfer to" }} {{ $application->facility->name }}.
                    </p>
                </div>

                <div class="clear-both"></div>

                <div class="col-md-10 col-md-offset-1">
                    {!! Form::textarea("statement") !!}
                </div>

                <div class="clear-both"></div>

                <div class="col-md-12 text-center">
                    <br />
                    {!! Button::success("SUBMIT SUPPORTING STATEMENT")->submit() !!}
                </div>

            </div>
            {!! Form::close() !!}
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
