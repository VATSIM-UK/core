@extends('layout')

@section('content')

    <div class="alert alert-danger">
		<h3 style="margin-top: 0">S2 and S3 Visiting and Transfers on Hold</h3>
		<p>Please note we are not currently accepting any applications for visiting or transferring S2 and S3 controllers. This is due to changes in policies and while we redesign the training process for visiting and transferring controllers.</p>
        <p>C1 visiting and transferring training materials are now completed and applications are now open. S1 and OBS rated members remain unaffected by this.</p>
	</div>
    
    <div class="col-md-9">

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-globe"></i> &thinsp; Visiting &
                        Transferring
                        Pilots & Controllers
                    </div>
                    <div class="panel-body">

                        <p>
                            This page is ONLY for members that wish to transfer to or visit the United Kingdom. &nbsp;If
                            you
                            wish to transfer or visit another region/division, you should apply directly to the <a
                                    href="https://vatsim.net/docs/about/regions" rel="external nofollow" target="_blank">team at
                                the
                                relevant region/division</a>&nbsp;you wish to go to.
                        </p>

                        <p>
                            VATSIM UK welcomes controllers from other divisions who wish to control our diverse range of
                            airfields and sectors. You only need to read this page if you are a home controller within a
                            division that is not the UK, and want either to transfer to the United Kingdom as a
                            controller,
                            or visit the United Kingdom as either a controller/pilot.
                        </p>

                        <p>
                            Please note that:
                        </p>

                        <ul>
                            <li>
                                All Transferring/Visiting controllers are subject to the global&nbsp;<a
                                        href="https://vatsim.net/docs/policy/transfer-and-visiting-controller-policy/"
                                        rel="external nofollow" target="_blank">VATSIM&nbsp;Transfer &amp; Visiting
                                    Controller Policy</a>&nbsp;and <a
                                        href="https://community.vatsim.uk/files/downloads/file/25-division-policy"
                                        rel="">VATSIM
                                    UK Division
                                    Policy</a>;
                            </li>
                            <li>
                                Only permanent controller ratings are relevant for visiting/transferring ATC
                                applications
                                (<strong>non permanent ratings of SUP/ADM/I1/I3 are not relevant</strong>);
                            </li>
                        </ul>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-plane"></i> Visiting Pilots
                    </div>
                    <div class="panel-body">
                        <p>
                            The United Kingdom Division is a well respected Authorised Training Organization providing
                            some of the highest standards of training. Our mentors and examiners excel in training
                            students to a high standard which allows us the maintain and improve our 89% pass rate over
                            more than 300 exams.
                        </p>

                        <p>
                            VATSIM United Kingdom currently offers training for P1 rating.&nbsp;
                        </p>

                        <p>
                            <a href="{{ route('visiting.landing') }}" rel="external nofollow">Apply as a visiting
                                pilot</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-headset"></i> Visiting Controllers
                    </div>
                    <div class="panel-body">
                        <p>
                            Please note that as per section 5.3 of division policy,&nbsp;VATSIM UK <strong>does not
                                accept visiting applications from S1 rated controllers</strong>, however you are welcome
                            to apply for full transfer (see below).
                        </p>

                        <p>
                            Visiting controllers are able to select one of the Visiting Groups from the list
                            (right) to visit. Visitors may also apply in a separate application for the Oceanic visiting group in
                            addition to any of the other groups.
                        </p>

                        <p>
                            <a href="{{ route('visiting.landing') }}" rel="external nofollow">Apply as a visiting
                                controller (including Oceanic - EGGX and Military)</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-headset"></i> Transferring Controllers
                    </div>
                    <div class="panel-body">

                        <p>The route to transferring to the United Kingdom depends on the rating that you currently
                            hold.</p>

                        <strong>If you hold the rating of OBS</strong>
                        <p>
                            OBS Rated members do not need to fill in a full application and should not use the
                            application system, instead raise a ticket to request the transfer by raising a ticket to
                            the Community (membership) Visit/Transfer option within&nbsp;our helpdesk:&nbsp;
                        </p>

                        <p>
                            <a href="https://helpdesk.vatsim.uk/" rel="external nofollow" target="_blank">Apply to
                                transfer as an OBS rated member</a>
                        </p>

                        <strong>If you do not hold the rating of OBS</strong>
                        <p>
                            There will be a single facility to transfer to called &#39;Transfer to the United Kingdom&#39;,
                            please choose this and complete the application to proceed. &nbsp;You will not be able to
                            choose the specific facility that your initial ratification takes place on.&nbsp;
                        </p>

                        <p>
                            <a href="{{ route('visiting.landing') }}" rel="external nofollow">Apply to transfer as
                                an S1/S2/S3/C1/C3 rated controller</a>
                        </p>

                    </div>
                </div>
            </div>

        </div>

    </div>


    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="fa fa-list"></i> &thinsp; Visiting Groups (ATC)
            </div>
            <div class="panel-body">
                <p>
                    <strong>Aerodrome Visiting Group (ADC VG)</strong>
                </p>

                <p>
                    All UK TWR/GND/DEL positions with the exception of EGLL
                    Military Aerodrome endorsement available on request. 
                </p>

                <p>
                    <strong>Approach Visiting Group (APP VG)</strong>
                </p>

                <p>
                    All UK APP + ADC VG positions with the exception of EGLL
                    Military Approach endorsement available on request. 
                </p>

                <p>
                    <strong>Enroute Visiting Group (ENR VG)</strong>
                </p>

                <p>
                    All UK CTR + ADC VG + APP VG positions with the exception of LON_S including splits.
                    Military Area endorsement available on request. 
                </p>

                <p>
                    <strong>Oceanic Visiting Group (OCA VG)</strong>
                </p>

                <p>
                    Shanwick Oceanic Positions 
                </p>
                
                <p>
                    <strong>Legacy Visiting Groups are listed in the Visiting and Transferring Controller Policy</strong>
                </p>

            </div>
        </div>
    </div>
@stop
