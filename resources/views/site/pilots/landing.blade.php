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
                            Join the waiting list <a href="{{ route('mship.waiting-lists.index') }}" target="_blank" rel="noopener noreferrer">here</a>
                        </li>
                        <li>
                            Sign up to the P1 PPL(A) moodle course <a href="https://moodle.vatsim.uk/course/view.php?id=51" target="_blank" rel="noopener noreferrer">here</a>.
                        </li>
                        <li>
                            Complete Theory Modules one, two and three.
                        </li>
                        <li>
                            Notify us <a href="https://helpdesk.vatsim.uk/open.php" target="_blank" rel="noopener noreferrer">here</a> that you have completed the Theory Phase of the P1 PPL(A) moodle course.
                        </li>
                        <li>
                            Sit tight! We will be in touch when a training place becomes available.
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
                        <li>
                            <a href="{{ route('visiting.landing') }}" rel="noreferrer noopener">Apply to visit as a Pilot</a> not seeing the option you want? Submit a ticket <a href="https://helpdesk.vatsim.uk/open.php" target="_blank" rel="noreferrer noopener">here</a>
                        </li>
                        <li>
                            When your V/T application has been accepted you will be added to the waiting list.
                        </li>
                        <li>
                            Sign up to the P1 PPL(A) moodle course <a href="https://moodle.vatsim.uk/course/view.php?id=51" target="_blank" rel="noopener noreferrer">here</a>.
                        </li>
                        <li>
                            Complete Theory Modules one, two and three.
                        </li>
                        <li>
                            Notify us <a href="https://helpdesk.vatsim.uk/open.php" target="_blank" rel="noreferrer noopener">here</a> that you have completed the Theory Phase of the P1 PPL(A) moodle course.
                        </li>
                        <li>
                            Sit tight! We will be in touch when a training place becomes available.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

@stop
