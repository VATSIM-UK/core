@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3 hidden-xs">
                    @if(isset($application))
                        {!! HTML::panelOpen($application->type_string. " Application #".$application->public_id.($application->facility ? " - ".$application->facility->name : ""), ["type" => "fa", "key" => "list"]) !!}
                    @else
                        {!! HTML::panelOpen("New Application", ["type" => "fa", "key" => "list"]) !!}
                    @endif
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            {{ link_to_route("visiting.landing", "Dashboard") }}
                        </li>

                        @if($application)
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 1 - T&amp;C Acceptance", [(isset($applicationType) ? $applicationType : $application->type)]) }}
                            </li>
                        @else
                            <li role="presentation" class="active">
                                {{ link_to_route("visiting.application.start", "Stage 1 - T&amp;C Acceptance", [(isset($applicationType) ? $applicationType : $application->type)]) }}
                            </li>
                        @endif


                        @can("select-facility", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.facility") ? 'class="active"' : "") !!}>
                                {{ link_to_route("visiting.application.facility", "Stage 2 - Facility Selection") }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 2 - Facility Selection") }}
                            </li>
                        @endif


                        @can("add-statement", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.statement") ? 'class="active"' : "") !!}>
                                {{ link_to_route("visiting.application.statement", "Stage 3 - Personal Statement".($application->statement_required ? "" : " (Not Required)")) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 3 - Personal Statement".($application->statement_required ? "" : " (Not Required)")) }}
                            </li>
                        @endif

                        @can("add-referee", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.referees") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.referees", "Stage 4 - Referees".($application->references_required > 0 ? "" : " (Not Required)")) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 4 - Referees".($application->references_required > 0 ? "" : " (Not Required)")) }}
                            </li>
                        @endcan

                        @can("submit-application", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.submit") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.submit", "Stage 5 - Submission") }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 5 - Submission") }}
                            </li>
                        @endif

                        @can("view", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.view") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.view", "View Full Application", [$application->public_id], ["class" => (Route::is("visiting.application.referees") ? "active" : "")]) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "View Full Application") }}
                            </li>
                        @endif
                    </ul>
                    {!! HTML::panelClose() !!}
                </div>
                <div class="col-md-9 hidden-xs">
                    @yield("vt-content")
                </div>
                <div class="col-cs-12 visible-xs">
                    <p>
                        You are unable to complete your visiting or transferring applications on a mobile device.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
