@extends('visit-transfer.site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3 hidden-xs" id="navBarHelp">
                    @if($application->exists)
                        @include('components.html.panel_open', [
                            'title' => $application->type_string. ' Application #'.$application->public_id.($application->facility ? ' - '.$application->facility->name : ''),
                            'icon' => ['type' => 'fa', 'key' => 'list']
                        ])
                    @else
                        @include('components.html.panel_open', [
                            'title' => 'New Application',
                            'icon' => ['type' => 'fa', 'key' => 'list']
                        ])
                    @endif
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            <a href="{{ route('visiting.landing') }}">Dashboard</a>
                        </li>

                        @if($application)
                            <li role="presentation" class="disabled">
                                <a href="#">Stage 1 - T&amp;C Acceptance</a>
                            </li>
                        @else
                            <li role="presentation" class="active">
                                <a href="{{ route('visiting.application.start', [(isset($applicationType) ? $applicationType : $application->type)]) }}">Stage 1 - T&amp;C Acceptance</a>
                            </li>
                        @endif


                        @can("select-facility", $application)
                            <li role="presentation" {!! (Route::is('visiting.application.facility') ? 'class="active"' : '') !!}>
                                <a href="{{ route('visiting.application.facility', [$application->public_id]) }}">Stage 2 - Facility Selection</a>
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                <a href="#">Stage 2 - Facility Selection</a>
                            </li>
                        @endif


                        @can("add-statement", $application)
                            <li role="presentation" {!! (Route::is('visiting.application.statement') ? 'class="active"' : '') !!}>
                                <a href="{{ route('visiting.application.statement', [$application->public_id]) }}">Stage 3 - Personal Statement{{ $application->statement_required ? '' : ' (Not Required)' }}</a>
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                <a href="#">Stage 3 - Personal Statement{{ $application->statement_required ? '' : ' (Not Required)' }}</a>
                            </li>
                        @endif

                        @can("submit-application", $application)
                            <li role="presentation" {!! (Route::is('visiting.application.submit') ? "class='active'" : '') !!}>
                                <a href="{{ route('visiting.application.submit', [$application->public_id]) }}">Stage 4 - Submission</a>
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                <a href="#">Stage 4 - Submission</a>
                            </li>
                        @endif

                        @can("view", $application)
                            <li role="presentation" {!! (Route::is('visiting.application.view') ? "class='active'" : '') !!}>
                                {{-- <a href="{{ route('visiting.application.view', [$application->public_id]) }}" class="{{ Route::is('visiting.application.referees') ? 'active' : '' }}">View Full Application</a> --}}
                            </li>
                        @else
                            <li role="presentation" class="disabled">
                                <a href="#">View Full Application</a>
                            </li>
                        @endif

                        @can("withdraw-application", $application)
                            <li role="presentation" class="text-center" {!! (Route::is('visiting.application.withdraw') ? "class='active'" : '') !!}>
                                <a href="{{ route('visiting.application.withdraw', [$application->public_id]) }}" class="label label-danger label-md">Withdraw Application</a>
                            </li>
                        @endif

                        @if($application->exists)
                            <li role="presentation">
                                <a class="label label-info label-md" style="white-space: initial;">
                                    Application expires in <span id="applicationExpireTimer"></span>
                                </a>
                            </li>
                        @endif
                    </ul>
                    @include('components.html.panel_close')
                </div>
                <div class="col-md-9 hidden-xs">
                    @yield("vt-content")
                </div>
                <div class="col-xs-12 visible-xs">
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

            return {
                'total': t,
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
