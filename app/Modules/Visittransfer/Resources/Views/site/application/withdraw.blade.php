@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row" id="submissionHelp">
        <div class="col-md-12">
            {!! HTML::panelOpen("Withdraw", ["type" => "fa", "key" => "tick"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        You may withdraw your application <strong>prior to it being submitted</strong> with no penalty.  To do so, please click the button below.
                        You <strong>will</strong> be able to open another application following this.
                    </p>

                    <p>
                        If you submit your application, you <strong>must</strong> contact the Community Department to process your cancellation request and <strong>may</strong>
                        be penalised.
                    </p>

                </div>

                {!! Form::horizontal(["route" => ["visiting.application.withdraw.post", $application->public_id], "method" => "POST"]) !!}
                    <div class="col-md-6 col-md-offset-3 text-center">
                        {!! Button::danger("WITHDRAW APPLICATION")->submit() !!}
                    </div>
                {!! Form::close() !!}

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
