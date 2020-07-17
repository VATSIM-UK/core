@extends('layout')

@section('content')

    <div class="col-md-9">

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Visiting &
                        Transferring
                        Pilots & Controllers
                    </div>
                    <div class="panel-body">

                        <p>
                            This page is ONLY for members that wish to transfer to or visit the United Kingdom. &nbsp;If
                            you
                            wish to transfer or visit another region/division, you should apply directly to the <a
                                    href="http://www.vatsim.net/region" rel="external nofollow" target="_blank">team at
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
                                All Transfering/Visiting controllers are subject to the global&nbsp;<a
                                        href="http://www.vatsim.net/documents/transfer-and-visiting-controller-policy"
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
                            <li>
                                All applications will be processed each Sunday.
                            </li>
                        </ul>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> Visiting Pilots
                    </div>
                    <div class="panel-body">
                        <p>
                            The United Kingdom Division is a well respected Authorised Training Organization providing
                            some of the highest standards of training. Our mentors and examiners excel in training
                            students to a high standard which allows us the maintain and improve our 89% pass rate over
                            more than 300 exams.
                        </p>

                        <p>
                            VATSIM United Kingdom currently offers training for P1/ P2 / P3 / P4 / P5 ratings.&nbsp;
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
                    <div class="panel-heading"><i class="glyphicon glyphicon-headphones"></i> Visiting Controllers
                    </div>
                    <div class="panel-body">
                        <p>
                            Please note that as per section 5.3 of division policy,&nbsp;VATSIM UK <strong>does not
                                accept visiting applications from S1 rated controllers</strong>, however you are welcome
                            to apply for full transfer (see below).
                        </p>

                        <p>
                            Visiting controllers are able to select one of the Visiting Groups (1-5) from the list
                            (right) to visit. Visitors may also apply in a separate application for groups 6 and 7 in
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
                    <div class="panel-heading"><i class="glyphicon glyphicon-headphones"></i> Transferring Controllers
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
            <div class="panel-heading"><i class="glyphicon glyphicon-time"></i> &thinsp; Visiting Groups (ATC)
            </div>
            <div class="panel-body">
                <p>
                    <strong>Visiting Group 1 (VG1) - S2+</strong>
                </p>

                <ul>
                    <li>
                        Newquay (EGHQ)
                    </li>
                    <li>
                        Cardiff (EGFF)
                    </li>
                    <li>
                        Jersey (EGJJ)
                    </li>
                    <li>
                        Guernsey (EGJB)
                    </li>
                    <li>
                        Bournemouth (EGHH)
                    </li>
                    <li>
                        Southampton (EGHI)
                    </li>
                    <li>
                        Exeter (EGTE)
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 2 (VG2) - S2+</strong>
                </p>

                <ul>
                    <li>
                        East Midlands (EGNX)
                    </li>
                    <li>
                        Cambridge (EGSC)
                    </li>
                    <li>
                        Norwich (EGSH)
                    </li>
                    <li>
                        London City (EGLC)
                    </li>
                    <li>
                        Biggin Hill (EGKB)
                    </li>
                    <li>
                        Southend-on-Sea (EGMC)
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 3 (VG3) - S2+</strong>
                </p>

                <ul>
                    <li>
                        Leeds Bradford (EGNM)
                    </li>
                    <li>
                        Doncaster Sheffield (EGCN)
                    </li>
                    <li>
                        Humberside (EGNJ)
                    </li>
                    <li>
                        Newcastle (EGNT)
                    </li>
                    <li>
                        Durham Tees Valley (EGNV)
                    </li>
                    <li>
                        Carlisle (EGNC)
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 4 (VG4) - S2+</strong>
                </p>

                <ul>
                    <li>
                        Blackpool (EGNH)
                    </li>
                    <li>
                        Isle of Man Ronaldsway (EGNS)
                    </li>
                    <li>
                        Belfast Aldergrove (EGAA)
                    </li>
                    <li>
                        Aberdeen (EGPD)
                    </li>
                    <li>
                        Inverness (EGPE)
                    </li>
                    <li>
                        Prestwick (EGPK)
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 5 (VG5) - C1+</strong>
                </p>

                <ul>
                    <li>
                        London Control (LON_W_CTR)
                    </li>
                    <li>
                        VG1/2/3/4 facilities
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 6 (VG6) - S2+</strong>
                </p>

                <ul>
                    <li>
                        All UK mainland military airfields
                    </li>
                    <li>
                        Gibraltar (LXGB)
                    </li>
                    <li>
                        RAF Akrotiri (LCRA)
                    </li>
                    <li>
                        RAF Mount Pleasent (EGYP)
                    </li>
                    <li>
                        RAF Ascension Island (FHAW)
                    </li>
                </ul>

                <p>
                    <strong>Visiting Group 7 (VG7) - C1+</strong>
                </p>

                <ul>
                    <li>
                        EGGX (Shanwick Oceanic)
                    </li>
                </ul>
            </div>
        </div>
    </div>
@stop
