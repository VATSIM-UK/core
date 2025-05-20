@extends ('layout')

@section('content')
    <div class="row equal">
        <div class="col-md-4 col-sm-offset-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-book"></i> &thinsp; A Guide To Flight Training
                    Exercises
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        You’ve made it!<br>
                        Here is a basic guide on what you need to do to get started with Flight Training Exercises.<br>
                        If you have any other questions then please <a href="https://helpdesk.vatsim.uk" target="_blank" rel="noopener noreferrer">contact us via our Helpdesk</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-6 col-sm-offset-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cog"></i> &thinsp; Configuration
                </div>
                <div class="panel-body">
                    <ol>
                        <li>
                            Download the smartCARS application
                            by <a href="https://vats.im/smartcars" target="_blank" rel="noopener noreferrer">clicking here</a>.
                        </li>
                        <li>
                            Install the smartCARS application by following the simple on-screen instructions.
                        </li>
                        <li>
                            We need a way to authenticate you. To do this, setup a secondary password by
                            <a href="{{ route('password.create') }}">clicking here</a>.
                        </li>
                        <li>
                            The setup is now complete and you are ready to book an exercise and use the application.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-6 col-sm-offset-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cloud"></i> &thinsp; Booking an Exercise
                </div>
                <div class="panel-body">
                    <ol>
                        <li>
                            Navigate to the <a href="{{ route('fte.dashboard') }}">Pilots &gt;&gt; Dashboard</a>.
                            <ul>
                                <li>Featured exercises will be displayed on the dashboard. A full list of exercises can be found by going to
                                    <a href="{{ route('fte.exercises') }}">Pilots &gt;&gt; Exercises</a>.</li>
                            </ul>
                        </li>
                        <li>
                            Once you have found an exercise you want to complete click the "View Details" button to see more information.
                        </li>
                        <li>
                            On the details page, you can find useful resources for the exercise including METARs, a map, briefings and statistics from previous flights.
                        </li>
                        <li>
                            Once you are ready to book an exercise, click the green "Book Flight" button.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-6 col-sm-offset-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plane"></i> &thinsp; Completing an Exercise
                </div>
                <div class="panel-body">
                    <ol>
                        <li>
                            Open the smartCARS application.
                        </li>
                        <li>
                            Login to the smartCARS application using the following credentials.
                            <ul>
                                <li>Username: <strong>VATSIM CID</strong></li>
                                <li>Password: <strong>Secondary Password</strong></li>
                            </ul>
                        </li>
                        <li>
                            Navigate to the <strong>FLIGHTS</strong> page to view your bookings.
                        </li>
                        <li>
                            Click on the exercise that you wish to complete and click <strong>Fly</strong>.
                        </li>
                        <li>
                            Select an aircraft in the first dropdown menu, "Flying on VATSIM" from the second dropdown menu and leave all other settings.
                        <li>
                            Load up at the departure aerodrome in your simulator of choice.
                            <ul>
                                <li>Note that you must have FSUIPC (or equivalent) installed for smartCARS to connect to your simulator.</li>
                            </ul>
                        </li>
                        <li>
                            Click <strong>Start</strong> on the smartCARS application.
                        </li>
                        <li>
                            Complete your flight as per the instructions found within the exercise brief.
                        </li>
                        <li>
                            When you have landed, apply your parking brakes, shutdown all engines and then click <strong>Finish</strong> and then <strong>File PIREP</strong> in smartCARS.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-5 col-sm-offset-1">
            <div class="panel panel-uk-success">
                <div class="panel-heading"><i class="fa fa-check-circle"></i> &thinsp; Pass?
                </div>
                <div class="panel-body">
                    Congratulations!<br>
                    Feel free to review your flight on the <a href="{{ route('fte.history') }}">flight history</a> page.<br>
                    <br>
                    Soon, completing Flight Training Exercises will earn you community points, awards and enter you into exclusive prize draws.
                    Keep an eye on our <a href="https://community.vatsim.uk" target="_blank" rel="noopener noreferrer">community forum</a> for the latest news.<br>
                    <br>
                    You are free to complete exercises as many times as you want, in whichever order you wish.<br>
                    <br>
                    Got feedback? Let us know...
                    <ul>
                        <li>On our <a href="https://community.vatsim.uk" target="_blank" rel="noopener noreferrer">Community Forum</a>.</li>
                        <li>Directly to us via <a href="https://helpdesk.vatsim.uk" target="_blank" rel="noopener noreferrer">our Helpdesk</a>.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-uk-danger">
                <div class="panel-heading"><i class="fa fa-question"></i> &thinsp; Fail?
                </div>
                <div class="panel-body">
                    Yikes!<br>
                    <br>
                    Our automated system checks your flight log when you submit your PIREP to us against a set list of criteria.<br>
                    This criteria is explained in each exercise's brief, available under <strong>Resources</strong> on the exercise's page.<br>
                    <br>
                    Your flight will fail for any combination of the following reasons...
                    <ul>
                        <li>You go outside our predefined 'limits' on the route,</li>
                        <li>You exceed one of the altitude restrictions defined in the brief,</li>
                        <li>You exceed one of the speed restrictions defined in the brief,</li>
                        <li>You use time acceleration on your simulator,</li>
                        <li>If you do not complete the exercise on the VATSIM network.</li>
                    </ul>
                    Make sure you review your attempt on the <a href="{{ route('fte.history') }}">flight history</a> page.<br>
                    You are welcome to attempt the exercise again, just go ahead and make another booking!
                </div>
            </div>
        </div>
    </div>

    <div class="row">
    </div>
@stop
