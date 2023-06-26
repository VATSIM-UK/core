@extends('layout')

@section('content')
    <div class="row">

        <div class="col-md-9">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-pencil"></i> &thinsp;Heathrow Endorsements
                </div>
                <div class="panel-body">
                    <p>

                    </p>
                    <h2>Background</h2>
                    <p>
                        London Heathrow (EGLL) is the only airfield in VATSIM UK that has been designated as a major
                        aerodrome under the regulations set out in VATSIM's Global Ratings Policy. Members rated S2 or higher
                        may undertake a number of special endorsements to be allowed to control Heathrow positions up to and
                        including their permanent controller rating. VATSIM UK does not offer training at Heathrow towards a
                        new permanent controller rating. These endorsements are also required for any controller that wishes to
                        open or be trained on the London South sector group or encompassing bandboxes.
                    </p>

                    <h2>Endorsements Offered</h2>
                    <p>
                        Heathrow offers a total of three endorsements:
                    </p>
                    <ul>
                        <li>Ground and delivery</li>
                        <li>Tower</li>
                        <li>Approach (Director)</li>
                    </ul>

                    <h2>Training Process</h2>
                    <p>
                        <img class="img-responsive" src="/images/heathrow-flow.svg" alt="Heathrow Flow Diagram" width=25%>
                    </p>

                    <ol>
                        <li>Complete the Moodle course for the endorsement you are requesting.
                            All members have access to the Heathrow Moodle courses
                            <ul>
                                <li>
                                    Heathrow Ground / Delivery <a href="https://moodle.vatsim.uk/course/view.php?id=28" target="_blank">here</a>
                                </li>
                                <li>
                                    Heathrow Tower <a href="https://moodle.vatsim.uk/course/view.php?id=30" target="_blank">here</a>
                                </li>
                                <li>
                                    Heathrow Approach <a href="https://moodle.vatsim.uk/course/view.php?id=29" target="_blank">here</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            Open a ticket at the VATSIM UK helpdesk (<a href="https://helpdesk.vatsim.uk/" target="_blank">here</a>) to request access to the theory exam.
                        </li>
                        <li>
                            Upon completion of the theory exam, reopen existing ticket to advise Heathrow staff of your exam pass.
                            (Should you fail the exam, a cooldown period of 7 days is applied automatically)
                        </li>
                        <li>
                            You will be added to the waiting list for the applicable endorsement. You can monitor you training place position <a href="https://www.vatsim.uk/mship/waiting-lists" target="_blank">here</a><br>
                            You <strong>must</strong> maintain eligibility in order to be considered for a training place. See below for eligibility requirements.
                        </li>
                        <li>
                            When available, a training place will be offered.
                        </li>
                        <li>
                            Permissions given to request practical training. A session request and availability are required in order to keep your training place.
                        </li>
                    </ol>

                    <h2>Recency & Eligibility Requirements</h2>
                    <h3><Strong>To be eligibile for a training place you are required to maintain the following requirements:</Strong></h3>
                    <ul>
                        <li>Ground / Delivery : <strong>12</strong> hours controlling UK positions within the preceeding 3 months.</li>
                        <li>Tower : <strong>12</strong> hours controlling UK positions within the preceeding 3 months, which at least <strong>5 hours</strong> must include EGLL_GND or _DEL positions.</li>
                        <li>Approach : <strong>12</strong> hours controlling UK positions within the preceeding 3 months, which at least <strong>5 hours</strong> must include EGLL_TWR positions.</li>
                    </ul>

                    <h2>Get Started</h2>
                    <p>
                        If you are interested in obtaining a Heathrow endorsement, please complete the relevant Moodle course then submit a ticket to ATC Training in the <a href="https://helpdesk.vatsim.uk/" target="_blank">helpdesk</a>
                        to request access to the theory exam.
                    </p>
		</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-question-sign"></i> &thinsp; FAQs
                </div>
                <div class="panel-body">

                    <p>
                        <strong>How long will it take to gain a Heathrow endorsement?</strong>
                    </p>

                    <p>
                        It varies from student to student, and with the amount of time you are able to dedicate to your training.
                    </p>

                    <p>
                        <strong>Where will training take place?</strong>
                    </p>

                    <p>
                        On the live network and via offline training (sweatbox).
                    </p>

                    <p>
                        <strong>What do I need before attending my sessions?</strong>
                    </p>

                    <ul>
                        <li>
                            An installed version of <a href="http://www.euroscope.hu/" rel="external nofollow">Euroscope</a>
                        </li>
                        <li>
                            <a href="https://community.vatsim.uk/files/downloads/file/61-uk-controller-pack/" rel="">UK Controller Pack</a>
                        </li>
						<li>
							<a href="https://vatsim.uk/ukcp" rel="external nofollow">UK Controller Plugin</a>
						</li>
						<li>
							<a href="https://audio.vatsim.net/docs/2.0/atc/euroscope" rel="external nofollow">Audio for VATSIM</a>
						</li>
                        <li>
                            <a href="{{ route('site.community.teamspeak') }}" rel="">Teamspeak</a>&nbsp;Installed
                        </li>

                    </ul>

                </div>
            </div>
        </div>
    </div>
@stop
