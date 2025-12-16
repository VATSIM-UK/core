@extends('layout')

@section('content')
	<div class="alert alert-danger">
		<h3 style="margin-top: 0">Very long waiting times - in excess of 1 year to begin ATC Training</h3>
		<p>Please note that the average time frame for observers joining our waiting list and being offered a training place exceeds one year. You should be prepared for this wait -  the division is working hard to improve training times, but demand is high. Thank you for your patience.</p>
	</div>
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-pencil"></i> &thinsp; New Controller
                </div>
                <div class="panel-body">
                    @include("site.atc.newcontroller-panel")
                    @include("site.atc.newcontroller-process")
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-question-sign"></i> &thinsp; FAQs
                </div>
                <div class="panel-body">
                    <p>
                        <strong>Where will my training take place?</strong>
                    </p>

                    <p>
                        Depending on demand, your training will take place at one of three training aerodromes: Edinburgh, Manchester or Stansted.&nbsp;
                    </p>

                    <p>
                        <strong>How long will it take to gain an S1 rating?</strong>
                    </p>

                    <p>
                        It varies from student to student, and with the amount of time you are able to dedicate to your training. Most students take two-three months after attending an introductory group session, any issues meaning this is not possible for you can be discussed with us and we will aim to accommodate.
                    </p>

                    <p>
                        <strong>What do I need before attending a group session?</strong>
                    </p>

                    <ul>
                        <li>
                            An installed version of <a href="http://www.euroscope.hu/" rel="external nofollow">Euroscope</a>
                        </li>
                        <li>
                            <a href="https://docs.vatsim.uk/General/Software%20Downloads/Controller%20Pack%20%26%20Sector%20File/" rel="">UK Controller Pack</a>
                        </li>
						<li>
							<a href="https://vatsim.uk/ukcp" rel="external nofollow">UK Controller Plugin</a>
						</li>
						<li>
							<a href="https://github.com/pierr3/TrackAudio/releases" rel="external nofollow">Track Audio</a>
						</li>
                        <li>
                            <a href="{{ route('site.community.teamspeak') }}" rel="">Teamspeak</a>&nbsp;Installed
                        </li>

                    </ul>

                    <p>
                        <strong>Where do I sign up for a group session?</strong>
                    </p>

                    <p>
                        You will be sent an invite to a group session via email when you are eligible for one. You can see group sessions (OBS_PT1) that are available&nbsp;to you via the <a href="https://cts.vatsim.uk/students/seminar.php" rel="external nofollow">&#39;Seminar&#39; section of the CTS. </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
