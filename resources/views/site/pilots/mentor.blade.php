@extends('layout')

@section('content')
    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-apple"></i> &thinsp; Becoming a Mentor
                </div>
                <div class="panel-body">
                    Interested in joining our team? The requirements to join the team are simple:<br/>

                    <ul>
                        <li>Hold the subsequent rating that you wish to mentor, e.g if you wish to mentor P1 then you
                            must hold a P1 rating,
                        </li>
                        <li>Be a member in good standing.</li>
                    </ul>

                    @if(Auth::user() && $_account->primary_state->code == 'DIVISION')
                        <strong>{{ $_account->name_first }}</strong>, <strong>you are</strong> a Division member, so you
                        can get started straight away!<br/>
                        You can find details on how to start mentoring below.
                    @elseif(Auth::user())
                        <strong>{{ $_account->name_first }}</strong>, <strong>you're not</strong> currently a Division
                        member!<br/>
                        You can find details on how to start mentoring below.
                    @else
                        You will need to be a member of VATSIM to become a mentor.<br/>
                        Already a member? <a href="{{ route('login') }}">Click here to login</a> and find out which
                        route is the most applicable to you.
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-4 col-md-offset-2"
             @if(Auth::user() && $_account->primary_state->code !== 'DIVISION')style="opacity: 0.3"@endif>
            <div class="panel panel-uk-success">
                <div class="panel-heading"><i class="glyphicon glyphicon-ok-circle"></i> &thinsp; I am a member of the
                    UK division
                </div>
                <div class="panel-body">
                    <ol>
                        <li>
                            <a href="https://helpdesk.vatsim.uk/" rel="external nofollow">Send us a ticket</a> using&nbsp;our
                            <strong>Helpdesk</strong>, letting us know what course you would like to mentor on
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
                            <em>e.g P1_MENTOR for the P1 (Online Pilot) Course mentor training.</em>
                        </li>
                        <li>
                            Add availability to the system and&nbsp;ensure this is kept up to date.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-md-4"
             @if(Auth::user() && $_account->primary_state->code == 'DIVISION')style="opacity: 0.3"@endif>
            <div class="panel panel-uk-danger">
                <div class="panel-heading"><i class="glyphicon glyphicon-remove-circle"></i> &thinsp; I am not a member
                    of the UK divison
                </div>
                <div class="panel-body">
                    <ol>
                        <li text="">
                            <a href="{{ route('visiting.landing') }}" rel="">Apply to visit as a
                                Pilot</a>.
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
                            <em>e.g P1_MENTOR for the P1 (Online Pilot) Course mentor training.</em>
                        </li>
                        <li>
                            Add availability to the system and&nbsp;ensure this is kept up to date.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

@stop

