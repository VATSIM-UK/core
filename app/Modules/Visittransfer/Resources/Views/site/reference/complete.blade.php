@extends('visittransfer::site.reference._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Reference Content", ["type" => "fa", "key" => "list"]) !!}
            <div class="row hidden-xs">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        You are complete a reference for {{ $application->account->name }}'s {{ $application->type_string }} application for {{ $application->facility->name }}.
                        This application is bound by the Visiting &amp; Transferring Controller Policy (VTCP).
                        <br />
                        {!! link_to("https://www.vatsim.net/documents/transfer-and-visiting-controller-policy", "The VTCP can be located on the VATSIM.net website", ["target" => "_blank"]) !!}
                    </p>

                    <p>
                        I can confirm the following:
                    </p>

                </div>

                {!! Form::horizontal(["route" => ["visiting.reference.complete.post", $token->code], "method" => "POST"]) !!}
                <div class="col-md-8 col-md-offset-2">
                    {!! ControlGroup::generate(
                        Form::label("reference_relationship", "I am ".$application->account->name."'s '".$reference->relationship."'&nbsp;&nbsp;"),
                        Form::checkbox("reference_relationship", true, false)
                    ) !!}

                    {!! ControlGroup::generate(
                        Form::label("reference_hours_minimum", $application->account->name." has consolidated their current controller rating as per the V&amp;T policy&nbsp;&nbsp;"),
                        Form::checkbox("reference_hours_minimum", true, false),
                            Form::help("A rating that has not been consolidated cannot be considered for a visit or transfer.")
                    ) !!}

                    {!! ControlGroup::generate(
                        Form::label("reference_recent_transfer", $application->account->name." last transferred region, division or VACC in excess of 90 days prior to ".$application->created_at->toDateString()."&nbsp;&nbsp;"),
                        Form::checkbox("reference_recent_transfer", true, false),
                        Form::help("Applicants may only transfer regions, divisions or VACCs once every 90 days.")
                    ) !!}

                    @if($application->type == \App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER)
                        {!! ControlGroup::generate(
                            Form::label("reference_not_staff", $application->account->name." will not hold a staff position in their home division if their application is successful&nbsp;&nbsp;"),
                            Form::checkbox("reference_not_staff", true, false),
                            Form::help("Members may only hold a staff position in their home division.")
                        ) !!}
                    @endif
                </div>

                <div class="col-md-10 col-md-offset-1">

                    <p>
                        Please provide a written reference for {{ $application->account->name }}, detailing why we should accept their request to {{ $application->type_string }} VATSIM UK.
                        <br />
                        <strong>The candidate will not be given automatic access to this content.</strong>
                    </p>

                </div>

                <div class="clear-both"></div>

                <div class="col-md-10 col-md-offset-1">
                    {!! Form::textarea("reference") !!}
                </div>

                <div class="clear-both"></div>

                <div class="col-md-12 text-center">
                    <br />
                    {!! Button::success("SUBMIT REFERENCE")->submit() !!}
                </div>

                {!! Form::hidden("application_type", $application->type) !!}

                {!! Form::close() !!}

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop