@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row" id="statementHelp">
        <div class="col-md-12">
            {!! HTML::panelOpen("Choose your Facility", ["type" => "fa", "key" => "question"]) !!}
            {!! Form::horizontal(["route" => ["visiting.application.statement.post", $application->public_id], "method" => "POST"]) !!}
            <div class="row">
                <div class="col-md-6 col-md-offset-3">

                    <p>
                        Please justify your application to the facility (<strong>{{ $application->facility->name }}</strong>) in the space provided below.
                        Remember to explain what your motivation is for applying to {{ $application->is_visit ? "visit" : "transfer to" }} <strong>{{ $application->facility->name }}</strong>.
                    </p>
                </div>

                <div class="clear-both"></div>

                <div class="col-md-10 col-md-offset-1">
                    {!! Form::textarea("statement", $application->statement) !!}
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