@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3 hidden-xs" id="navBarHelp">
                    @if($application->exists)
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
                                {{ link_to_route("visiting.application.facility", "Stage 2 - Facility Selection", [$application->public_id]) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 2 - Facility Selection") }}
                            </li>
                        @endif


                        @can("add-statement", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.statement") ? 'class="active"' : "") !!}>
                                {{ link_to_route("visiting.application.statement", "Stage 3 - Personal Statement".($application->statement_required ? "" : " (Not Required)"), [$application->public_id]) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 3 - Personal Statement".($application->statement_required ? "" : " (Not Required)")) }}
                            </li>
                        @endif

                        @can("add-referee", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.referees") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.referees", "Stage 4 - Referees".($application->references_required > 0 ? "" : " (Not Required)"), [$application->public_id]) }}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                {{ link_to("#", "Stage 4 - Referees".($application->references_required > 0 ? "" : " (Not Required)")) }}
                            </li>
                        @endcan

                        @can("submit-application", $application)
                            <li role="presentation" {!! (Route::is("visiting.application.submit") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.submit", "Stage 5 - Submission", [$application->public_id]) }}
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

                        @can("withdraw-application", $application)
                            <li role="presentation" class="text-center" {!! (Route::is("visiting.application.withdraw") ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.withdraw", "Withdraw Application", [$application->public_id], ["class" => "label label-danger label-md"]) }}
                            </li>
                        @endif

                        @if($application->exists)
                            <li role="presentation">
                                <a class="label label-info label-md">
                                    Application expires in <span id="applicationExpireTimer"></span>
                                </a>
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

@section("scripts")
    @parent

    <script type="text/javascript">
        function initializeClock(id, endtime){
            var clock = document.getElementById(id);
            var timeinterval = setInterval(function(){
                var t = getTimeRemaining(endtime);
                clock.innerHTML = '';

                if(t.minutes > 1){
                    clock.innerHTML += t.minutes + ' minutes';
                } else if(t.minutes == 1){
                    clock.innerHTML += t.minutes + ' minute';
                }

                if(t.minutes > 0 && t.seconds > 0){
                    clock.innerHTML += " and ";
                }

                if(t.seconds > 1){
                    clock.innerHTML += t.seconds + ' seconds';
                } else if(t.seconds == 1){
                    clock.innerHTML += t.seconds + ' second';
                }

                clock.innerHTML += ".";

                if(t.total<=0){
                    clearInterval(timeinterval);
                }
            },1000);
        }

        function getTimeRemaining(endtime){
            var t = Date.parse(endtime) - Date.parse(new Date());
            var seconds = Math.floor( (t/1000) % 60 );
            var minutes = Math.floor( (t/1000/60) % 60 );
//            var hours = Math.floor( (t/(1000*60*60)) % 24 );
//            var days = Math.floor( t/(1000*60*60*24) );
            return {
                'total': t,
//                'days': days,
//                'hours': hours,
                'minutes': minutes,
                'seconds': seconds
            };
        }

        @if($application->exists && $application->expires_at !== null)
            initializeClock('applicationExpireTimer', "{{ $application->expires_at->toDateTimeString() }} GMT");
        @endif

        var tour = new Tour({
            name: "VT-Application-{{ Request::segment(2) }}"
        });

        @if(Request::segment(2) == "start")

        tour.addStep({
            element: "#termsBoxHelp",
            title: "Accept the terms",
            content: "You should read the statements on this page carefully and only agree if they are true.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#navBarHelp",
            title: "Navigation",
            content: "You can use this navigation menu to move between sections of your application as necessary.",
            backdrop: true,
            placement: "right"
        });

        @endif

        @if(Request::segment(2) == "facility")

                tour.addStep({
            element: "#labelTrainingHelp",
            title: "Facilities Requiring Training",
            content: "Where a facility does require training, you may experience a brief delay in your application being fully completed.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#labelNoTrainingHelp",
            title: "Facilities Not Requiring Training",
            content: "Where a facility doesn't require any training, your application will progress much quicker.",
            backdrop: true,
            placement: "top"
        });

        @endif

        @if(Request::segment(2) == "referees")

            tour.addStep({
            element: "#minReferencesHelp",
            title: "Minimum References",
            content: "There is a minimum reference requirement for this application.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#divisionStaffHelp",
            title: "Division Staff",
            content: "Your referees <strong>must</strong> be staff within your home division.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#trainingStaffHelp",
            title: "Training Director",
            content: "One of your referees <strong>must</strong> be your training director.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#refereeCidHelp",
            title: "Referee CID",
            content: "Please enter a valid VATSIM CID for your referee.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#refereePositionHelp",
            title: "Referee Staff Position",
            content: "Enter a reflective staff title.",
            backdrop: true,
            placement: "top"
        });

        tour.addStep({
            element: "#refereeEmail",
            title: "Referee E-Mail",
            content: "Enter the E-Mail address we should write to.",
            backdrop: true,
            placement: "top"
        });

        @endif

        @if(Request::segment(2) == "statement")

            tour.addStep({
            element: "#statementHelp",
            title: "Personal Statement",
            content: "It is expected that you will describe why you wish to apply to your chosen facility.",
            backdrop: true,
            placement: "top"
        });

        @endif

        @if(Request::segment(2) == "submit")

            tour.addStep({
            element: "#submissionHelp",
            title: "Submit Application",
            content: "Once your application has been submitted it <strong>cannot</strong> be withdrawn.",
            backdrop: true,
            placement: "top"
        });

        @endif

        tour.init();
        tour.start();
    </script>
@stop
