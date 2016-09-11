@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row" id="termsBoxHelp">
        <div class="col-md-12">
            {!! HTML::panelOpen("Terms &amp; Conditions", ["type" => "fa", "key" => "list"]) !!}
            <div class="row hidden-xs">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        Before you can start your application, you must first read and agree to the terms and conditions of the
                        Visiting &amp; Transferring Controller Policy (VTCP).
                        {!! link_to("https://www.vatsim.net/documents/transfer-and-visiting-controller-policy", "The VTCP can be located on the VATSIM.net website", ["target" => "_blank"]) !!}
                    </p>

                </div>

                {!! Form::horizontal(["route" => ["visiting.application.start.post", $applicationType, $trainingTeam], "method" => "POST"]) !!}
                    <div class="col-md-8 col-md-offset-2">
                        {!! ControlGroup::generate(
                            Form::label("terms_read", "I confirm that I have read the Visiting &amp; Transferring Controller Policy&nbsp;&nbsp;"),
                            Form::checkbox("terms_read", true, false),
                            Form::help("Your application will be rejected if you do not meet the requirements of this policy.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_one_hour", "I confirm that I will complete this application within 60 minutes&nbsp;&nbsp;"),
                            Form::checkbox("terms_one_hour", true, false),
                            Form::help("After 60 minutes your application will automatically be deleted if it isn't submitted.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_hours_minimum", "I confirm that I have consolidated my rating by controlling for 50 hours in my home division&nbsp;&nbsp;"),
                            Form::checkbox("terms_hours_minimum", true, false),
                            Form::help("A rating that has not been consolidated cannot be considered for an application to visit or transfer.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_hours_minimum_relevant", "When consolidating my current rating, the hours were spent on a suitable position for my rating&nbsp;&nbsp;"),
                            Form::checkbox("terms_hours_minimum_relevant", true, false),
                            Form::help("For example, if you are a C1 your 50 hours must have been spent on CTR or FSS type positions.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_recent_transfer", "I last transferred region, division or VACC at least 90 days prior to the start of my application&nbsp;&nbsp;"),
                            Form::checkbox("terms_recent_transfer", true, false),
                            Form::help("Applicants may only transfer regions, divisions or VACCs once every 90 days.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_90_day", "I will complete my local induction plan (if required), or make every attempt to attain full competency within 90 days&nbsp;&nbsp;"),
                            Form::checkbox("terms_90_day", true, false),
                            Form::help("Any application not completing this induction will be transferred back to their previous region/division.")
                        ) !!}

                        @if($applicationType == \App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER)
                            {!! ControlGroup::generate(
                                Form::label("terms_not_staff", "I confirm that I will not hold a staff position in another region, division or VACC if my application is successful&nbsp;&nbsp;"),
                                Form::checkbox("terms_not_staff", true, false),
                                Form::help("You may only hold a staff position in your home division.")
                            ) !!}
                        @endif
                    </div>

                    <div class="col-md-6 col-md-offset-3 text-center">
                        {!! Button::success("START APPLICATION")->submit() !!}
                    </div>

                {!! Form::hidden("application_type", $applicationType) !!}
                {!! Form::hidden("training_team", $trainingTeam) !!}

                {!! Form::close() !!}

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop