@extends ('layout')

@section('content')
    <div class="row equal">
        <div class="col-md-3">
            <div class="panel panel-ukblue" id="gettingStarted">
                <div class="panel-heading"><i class="fa fa-book"></i> &thinsp; Getting Started
                </div>
                <div class="panel-body text-center">
                    <a href="{{route('fte.guide')}}" style="text-decoration: none;">
                        <img src="{{ asset('images/book.png') }}" alt="world" width="80" height="80">
                    </a>
                    <br><br>
                    Check out this guide for how to get started.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-ukblue" id="welcomeBox">
                <div class="panel-heading"><i class="fa fa-plane-departure"></i> &thinsp; Flight Training Exercises
                </div>
                <div class="panel-body">
                        Fancy something different? VATSIM UK is proud to announce the launch of Flight Training Exercises –
                        the new
                        way to learn and have fun!<br/><br/>
                        Choose any one of our exercises and take flight, discovering the South East of the UK
                        and much more.<br>
                        To get started check out <a href="{{ route('fte.guide') }}">our guide here</a>.<br/><br/>
                        If you have any questions please contact the Pilot Training Department via the Helpdesk
                        (<a href="https://helpdesk.vatsim.uk/" target="_blank" rel="noopener noreferrer">click here</a>).<br><br>
                        <button type="button" id="restart_tour" class="btn btn-success center-block">Take a Tour</button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-ukblue" id="flightHistory">
                <div class="panel-heading"><i class="fa fa-clock"></i> &thinsp; Past Flights
                </div>
                <div class="panel-body text-center">
                    <a href="{{route('fte.history')}}" style="text-decoration: none;">
                        <img src="{{ asset('images/history.png') }}" alt="history" width="80" height="80">
                    </a>
                    <br><br>
                    View flight history.
                </div>
            </div>
        </div>
    </div>

    <div class="row row-flex" id="exercises">
        @foreach($exercises as $exercise)
            <div class="col-md-{{ 12 / $exercises->count() }}">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Featured - {{ $exercise->name }}</div>
                    <div class="panel-body">
                        @if($exercise->image)
                            <div class="text-center">
                                <img src="{{ $exercise->image }}" class="img-responsive center-block" alt="{{ $exercise->name }}">
                            </div>
                        @endif
                        <p style="margin-top: 10px;">{{ $exercise->description }}</p>
                        <div class="panel-base text-right">
                            <a href="{{ route('fte.exercises', $exercise) }}" class="btn btn-primary">View Details &gt;&gt;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel" id="allExercises">
                    <div class="panel-body">
                        <div class="text-center">
                            <a href="{{ route('fte.exercises') }}" class="btn btn-primary">View All Exercises &gt;&gt;</a>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@stop

@if($pireps == 0)
@section("scripts")
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tour/0.11.0/js/bootstrap-tour.min.js" integrity="sha384-vzCaHnPHCvqX/NZEoFP8o6Kl3oz4t69lFsHpZ8uIzr+NURIp0PoavFo0OXXchs3V" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var tour = new Tour({
            name: "FTEGuide",
            steps: [
                {
                    element: "#welcomeBox",
                    title: "Welcome!",
                    content: "Welcome to Flight Training Exercises.<br>Let us show you around.",
                    backdrop: true,
                    placement: "top"
                },

                {
                    element: "#exercises",
                    title: "Featured Exercises",
                    content: "Here you will find currently featured exercises.",
                    backdrop: true,
                    placement: "top"
                },

                {
                    element: "#allExercises",
                    title: "There's More...",
                    content: "A full list of exercises is available here.",
                    backdrop: true,
                    placement: "bottom"
                },

                {
                    element: "#flightHistory",
                    title: "Flight History",
                    content: "Feel free to review your previous flights here.",
                    backdrop: true,
                    placement: "left"
                },

                {
                    element: "#gettingStarted",
                    title: "Getting Started",
                    content: "Now you've navigated your way around, click here to find out how to get started!",
                    backdrop: true,
                    placement: "right"
                },
            ]
        });

        tour.init();
        tour.start();
        $("#restart_tour").click(function(){
            tour.restart();
        });
    </script>
@stop
@endif
