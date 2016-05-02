@extends('visittransfer::application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Terms &amp; Conditions", ["type" => "fa", "key" => "list"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        Before you can start your application, you must first read and agree to the terms and conditions of the
                        Visiting &amp; Transferring Controller Policy (VTCP).
                        {!! link_to("https://www.vatsim.net/documents/transfer-and-visiting-controller-policy", "The VTCP can be located on the VATSIM.net website", ["target" => "_blank"]) !!}
                    </p>

                </div>

                {!! Form::horizontal(["route" => ["visiting.application.start.post", $applicationType], "method" => "POST"]) !!}
                    <div class="col-md-8 col-md-offset-2">
                        {!! ControlGroup::generate(
                            Form::label("terms_read", "I confirm that I have read the Visiting &amp; Transferring Controller Policy&nbsp;&nbsp;"),
                            Form::checkbox("terms_read", true, false),
                            Form::help("Your application will be rejected if it is later found you haven't read this document.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_hours_minimum", "I confirm that I have consolidated my rating by controlling for 50 hours in my home division&nbsp;&nbsp;"),
                            Form::checkbox("terms_hours_minimum", true, false),
                            Form::help("A rating that has not been consolidated cannot be considered for a visit or transfer.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_hours_minimum_relevant", "When consolidating my current rating, the hours were spent on a suitable position for my rating&nbsp;&nbsp;"),
                            Form::checkbox("terms_hours_minimum_relevant", true, false),
                            Form::help("For example, if you are a C1 your 50 hours must have been spent on CTR or FSS type positions.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_recent_transfer", "I confirm that I last transferred region, division or VACC in excess of 90 days prior to the start of my application&nbsp;&nbsp;"),
                            Form::checkbox("terms_recent_transfer", true, false),
                            Form::help("Applicants may only transfer regions, divisions or VACCs once every 90 days.")
                        ) !!}

                        {!! ControlGroup::generate(
                            Form::label("terms_90_day", "I understand that I must complete my local induction plan (if required), or attain full competency within 90 days&nbsp;&nbsp;"),
                            Form::checkbox("terms_90_day", true, false),
                            Form::help("Any application not completing this induction will be transferred back to their previous region/division.")
                        ) !!}

                        @if($applicationType == \App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER)
                            {!! ControlGroup::generate(
                                Form::label("terms_not_staff", "I confirm that I am not presently staff in my home division OR that if I am staff, I will relinquish my role if successful&nbsp;&nbsp;"),
                                Form::checkbox("terms_not_staff", true, false),
                                Form::help("You may only hold a staff position in your home division.")
                            ) !!}
                        @endif
                    </div>

                    <div class="col-md-6 col-md-offset-3 text-center">
                        {!! Button::success("START APPLICATION")->submit() !!}
                    </div>

                {!! Form::hidden("application_type", $applicationType) !!}

                {!! Form::close() !!}

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
