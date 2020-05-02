@extends('layout')

@section('content')

        <div class="row">

            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading">
                        <i class="fab fa-discord"></i> &thinsp; Discord Registration
                    </div>
                    <div class="panel-body">
                        <p>
                            Our community Discord server is the place to go to chat to other members of the UK Division and the wider network.<br />
                            Registration should take you less than 60 seconds!
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-4 col-md-offset-2">
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

            <div class="col-md-4">
                <div class="panel panel-uk-danger">
                    <div class="panel-heading"><i class="glyphicon glyphicon-remove-circle"></i> &thinsp; I am not a member
                        of the UK divison
                    </div>
                    <div class="panel-body">
                        <ol>
                            <li>
                                When your V/T application has been processed you will be contacted by the Pilot Training
                                Team using our HelpDesk.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
@stop
