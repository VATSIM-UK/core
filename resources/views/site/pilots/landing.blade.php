@extends('layout')

@section('content')

    <div class="row row-flex">

        <div class="col-md-8 col-md-offset-2">
            <img class="img-responsive center-block"
                 src="/images/pilottrainingheader.png"/>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-uk-danger">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; NOTAM
                </div>
                <div class="panel-body">
                    <p>Recently VATSIM.net announced that the pilot rating system will be restructured from September 1st. The new system will more closely emulate the structure of ‘real-world’ pilot training, and will increase the consistency of training provided by all Authorised Training Organisations (ATOs) through more strictly defined course syllabi.</p>

                    <p>As a result of this we are no longer accepting new students into our training system to complete the P1 (Online Pilot) course as this rating will be handled centrally by VATSIM.net. The training team is operating at maximum capacity to try and meet the deadline set out by VATSIM.net, and continue to provide our members with the opportunity to expand their knowledge of aviation.</p>

                    <p>We look forward seeing you training in VATSIM UK in the future.</p>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Pilot Training
                </div>
                <div class="panel-body">
                    <p>Welcome to VATSIM UK’s Pilot Training Department.</p>

                    <p>Who said learning can’t be fun! Here at the Pilot Training Department in VATSIM UK we believe
                        that the key to enjoying the network to its full potential is Pilot Training. Pilot Training
                        offers you the opportunity to learn and understand the simple and more complex elements of
                        flying allowing you to really test those controllers!</p>

                    <p>The United Kingdom Division is a well respected Authorised Training Organization providing some
                        of the highest standards of training. Our mentors and examiners excel in training students to a
                        high standard which allows us the maintain and improve our 89% pass rate over more than 300
                        exams.</p>

                    <p>Our training packs contain everything you will need for each rating, so there is no need to go
                        searching for the answers, we provided everything.</p>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; How To Enrol
                </div>
                <div class="panel-body">
                    @if(Auth::user() && $_account->primary_state->code == 'DIVISION')
                        <strong>{{ $_account->name_first }}</strong>, <strong>you are</strong> a Division member, so you can get started straight away!<br />
                        You can find details on how to sign up for training in the UK below.
                    @elseif(Auth::user())
                        <strong>{{ $_account->name_first }}</strong>, <strong>you're not</strong> currently a Division member!<br />
                        You can find details on how to sign up for training in the UK below.
                    @else
                        You will need to be a member of VATSIM to sign up to our training courses.<br />
                        Already a member? {!! HTML::link(route('login'), "Click here to login") !!} and find out which route is the most applicable to you.
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-4 col-md-offset-2" @if(Auth::user() && $_account->primary_state->code !== 'DIVISION')style="opacity: 0.3"@endif>
            <div class="panel panel-uk-success">
                <div class="panel-heading"><i class="glyphicon glyphicon-ok-circle"></i> &thinsp; I am a member of the
                    UK division
                </div>
                <div class="panel-body">
                    <ol>
                        <li>
                            Send us a ticket using&nbsp;our <strong>Helpdesk</strong> <a
                                    href="https://helpdesk.vatsim.uk/" rel="external nofollow">here</a>, letting us know
                            what course you would like to enrol on.
                        </li>
                        <li>
                            You will receive&nbsp;a response within <strong>72 hours</strong>.
                        </li>
                        <li>
                            You will either be added to the waiting list or you will be informed that your mentoring
                            permissions have been assigned.
                        </li>
                        <li>
                            Once your mentoring permissions have been assigned navigate to our <a
                                    href="https://cts.vatsim.uk/" rel="external nofollow">Central Training System
                                (CTS</a>).
                        </li>
                        <li>
                            Sign into the CTS using our SSO.
                        </li>
                        <li>
                            Select the Students Drop down menu and navigate to <strong>Sessions &gt; Managment</strong>
                        </li>
                        <li>
                            Add a session request using the <strong>&#39;Request Session&#39;</strong> drop down box,
                            <em>e.g P1_VATSIM for the P1 (Online Pilot) Course</em>
                        </li>
                        <li>
                            Add availability to the system and&nbsp;ensure this is kept up to date.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-md-4" @if(Auth::user() && $_account->primary_state->code == 'DIVISION')style="opacity: 0.3"@endif>
            <div class="panel panel-uk-danger">
                <div class="panel-heading"><i class="glyphicon glyphicon-remove-circle"></i> &thinsp; I am not a member
                    of the UK divison
                </div>
                <div class="panel-body">
                    <ol>
                        <li text="">
                            <a href="{{ route('visiting.landing') }}" rel="">Apply to visit as a
                                Pilot</a>
                        </li>
                        <li text="">
                            When your V/T application has been processed you will be contacted by the Pilot Training
                            Team using our HelpDesk.
                        </li>
                        <li>
                            You will either be added to the waiting list or you will be informed that your mentoring
                            permissions have been assigned.
                        </li>
                        <li>
                            Once your mentoring permissions have been assigned navigate to our <a
                                    href="https://cts.vatsim.uk/" rel="external nofollow">Central Training System
                                (CTS</a>).
                        </li>
                        <li>
                            Sign into the CTS using our SSO.
                        </li>
                        <li>
                            Select the Students Drop down menu and navigate to <strong>Sessions &gt; Managment</strong>
                        </li>
                        <li>
                            Add a session request using the <strong>&#39;Request Session&#39;</strong> drop down box,
                            <em>e.g P1_VATSIM for the P1 (Online Pilot) Course</em>
                        </li>
                        <li>
                            Add availability to the system and ensure this is kept up to date.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

@stop
