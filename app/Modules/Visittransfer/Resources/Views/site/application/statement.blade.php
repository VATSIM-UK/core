@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row" id="statementHelp">
        <div class="col-md-12">
            {!! HTML::panelOpen("Choose your Facility", ["type" => "fa", "key" => "question"]) !!}
            {!! Form::horizontal(["route" => ["visiting.application.statement.post"], "method" => "POST"]) !!}
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

@section("scripts")
    @parent

    <script type="text/javascript">
        var tour = new Tour({
            steps: [
                {
                    element: "#statementHelp",
                    title: "Personal Statement",
                    content: "It is expected that you will describe why you wish to apply to your chosen facility.",
                    backdrop: true,
                    placement: "top"
                },
            ]
        });

        tour.init();
        tour.start();
    </script>
@stop