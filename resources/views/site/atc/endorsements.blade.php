@extends('layout')

@section('content')

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-headset" aria-hidden="true"></i> &thinsp; ATC Endorsements
                </div>
                <div class="panel-body">

                <p>
                    Once you have achieved a given controller rating, you are free to control the vast majority of
                    airports/sectors within the UK that are applicable to that rating.<br/><br/>
                    There are, however, a few exceptions. Some of our busier positions will require you to gain what
                    is known as an <strong>Endorsement</strong>.
                </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-gatwickgnd">
                    <div class="panel-heading">
                        <i class="fa fa-plane-departure" aria-hidden="true"></i> &thinsp; London Gatwick - GND (S1)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-gatwickgnd" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>

                    <p>
                        Controlling at London Gatwick is restricted to S2 rated members, or S1s that hold a special endorsement.
                        This restriction for S1s is in place to allow members to gain experience in quieter environments
                        and practice their skills before tackling the workload at Gatwick.
                    </p>

                    <p>
                        The Gatwick (S1) Endorsement enables controllers to open Ground (EGKK_GND) or Delivery (EGKK_DEL).
                    </p>

                    <h3>Endorsement Process</h3>

                    <p>
                        View the requirements for the Gatwick Ground endorsement by <a
                            href="{{ route('controllers.endorsements.gatwick_ground') }}">clicking here</a>.
                    <p>

                    <h3>Get Started</h3>

                    <p>
                        The process for getting started with the Gatwick Ground endorsement can be found by <a
                            href="{{ route('controllers.endorsements.gatwick_ground') }}">clicking here</a>.
                    </p>

                </div>
            </div>
        </div>


        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-heathrow-s1">
                    <div class="panel-heading">
                        <i class="fa fa-plane-departure" aria-hidden="true"></i> &thinsp; London Heathrow - GND (S1)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-heathrow-s1" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>

                    <p>
                        London Heathrow (EGLL) is a Tier 1 aerodrome and the busiest airport on the VATSIM network.

                        Controlling at London Heathrow is restricted, members must hold a special endorsement.

                        This restriction is in place to allow members to gain experience in quieter environments
                        and practice their skills before tackling the workload at Heathrow.

                        S1 rated members that hold a Gatwick Endorsement may train for a Heathrow Ground (S1) Endorsement.
                    </p>

                    <h3>Endorsement Process</h3>

                    <p>
                        View the requirements for the Heathrow Ground (S1) endorsement by <a
                            href="{{ route('controllers.endorsements.heathrow_ground_s1') }}">clicking here</a>.
                    <p>

                    <h3>Get Started</h3>

                    <p>
                        The process for getting started with the Heathrow Ground (S1) endorsement can be found by <a
                            href="{{ route('controllers.endorsements.heathrow_ground_s1') }}">clicking here</a>.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-afis">
                    <div class="panel-heading">
                        <i class="fa fa-info" aria-hidden="true"></i> &thinsp; AFIS/AGCS (S1)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-afis" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>

                    <p>
                        VATSIM UK has a large number of pilots that enjoy a little jaunt across the country in a light
                        aircraft, enjoying the sights and freedoms of VFR. We also have a number of small aerodromes that
                        are hotspots for this activity, which is now tied in with the Pilot Training Department's Flight
                        Training Exercises (FTEs). These smaller aerodromes do not provide a full Air Traffic Service, but
                        instead either an Aerodrome Flight Information Service (AFIS) or Air/Ground Communications Service
                        (AGCS).
                    </p>

                    <h3>Requirements</h3>

                    <p>
                        As such, we offer S1s the opportunity to become endorsed on a select number of these positions
                        through an online self-learning (Moodle) course. This course may be enrolled on at any time by
                        any member. The endorsement process is as follows:
                    </p>

                    <ol>
                        <li>In order to be eligible to take the Moodle exam, the member must have acquired a total of 50 hours controlling on ground and delivery positions in the UK</li>
                        <li>Once the hour requirement is met, the member submits a ticket to the helpdesk asking to be enrolled in the Moodle exam</li>
                        <li>The member must pass the exam at the end of the Moodle course and informs the training department via the helpdesk that they have passed.</li>
                    </ol>

                    <h3>Approved Aerodromes</h3>
                    <ul>
                        <li>Andrewsfield (EGSL)</li>
                        <li>Barra (EGPR)</li>
                        <li>Bedford (EGBF)</li>
                        <li>Bembridge (EGHJ)</li>
                        <li>Blackbushe (EGLK)</li>
                        <li>Bodmin (EGLA)</li>
                        <li>Caernarfon (EGCK)</li>
                        <li>Campbeltown (EGEC)</li>
                        <li>Chalgrove (EGLJ)</li>
                        <li>Chichester Goodwood (EGHR)</li>
                        <li>Clacton (EGSQ)</li>
                        <li>Coll (EGEL)</li>
                        <li>Colonsay (EGEY)</li>
                        <li>Compton Abbas (EGHA)</li>
                        <li>Coventry (EGBE)</li>
                        <li>Cumbernauld (EGPG)</li>
                        <li>Denham (EGLD)</li>
                        <li>Derby (EGBD)</li>
                        <li>Dunkeswell (EGTU)</li>
                        <li>Duxford (EGSU)</li>
                        <li>Earls Colne (EGSR)</li>
                        <li>Elstree (EGTR)</li>
                        <li>Enniskillen/St Angelo (EGAB)</li>
                        <li>Fair Isle (EGEF)</li>
                        <li>Fairoaks (EGTF)</li>
                        <li>Fenland (EGCL)</li>
                        <li>Gamston (EGNE)</li>
                        <li>Haverfordwest (EGFE)</li>
                        <li>Islay (EGPI)</li>
                        <li>Kemble (EGBP)</li>
                        <li>Lashenden/Headcorn (EGKH)</li>
                        <li>Lee-on-Solent (EGHF)</li>
                        <li>Leeds East/Fenton (EGCM)</li>
                        <li>Leicester (EGBG)</li>
                        <li>Lerwick/Tingwall (EGET)</li>
                        <li>Manchester Barton (EGCB)</li>
                        <li>Netherthorpe (EGNF)</li>
                        <li>Newtownards (EGAD)</li>
                        <li>Northampton/Sywell (EGBK)</li>
                        <li>Nottingham (EGBN)</li>
                        <li>Oban (EGEO)</li>
                        <li>Old Buckenham (EGSV)</li>
                        <li>Old Sarum (EGLS)</li>
                        <li>Old Warden (EGTH)</li>
                        <li>Penzance (EGHK)</li>
                        <li>Perth (EGPT)</li>
                        <li>Portland (EGDP)</li>
                        <li>Rochester (EGTO)</li>
                        <li>Sandtoft (EGCF)</li>
                        <li>Sherburn-in-Elmet (EGCJ)</li>
                        <li>Shobdon (EGBS)</li>
                        <li>Sleap (EGCV)</li>
                        <li>Stapleford (EGSG)</li>
                        <li>Swansea (EGFH)</li>
                        <li>Tatenhill (EGBM)</li>
                        <li>Thruxton (EGHO)</li>
                        <li>Tiree (EGPU)</li>
                        <li>Tresco (EGHT)</li>
                        <li>Walney (EGNL)</li>
                        <li>Wellesbourne (EGBW)</li>
                        <li>Welshpool (EGCW)</li>
                        <li>West Wales/Aberporth (EGFA)</li>
                        <li>White Waltham (EGLM)</li>
                        <li>Wickenby (EGNW)</li>
                        <li>Wolverhampton / Halfpenny Green (EGBO)</li>
                        <li>Wycombe Air Park/Booker (EGTB)</li>
                        <li>Stanley (SFAL) - see <a href="https://community.vatsim.uk/files/downloads/file/36-vatsim-argentina-comodoro-rivadavia-acc-vatsim-uk-mount-pleasant-ats-unit-loa/">Argentina LoA</a></li>
                    </ul>

                    <h3>Get Started</h3>

                    <p>
                        Once you have met the minimum hour requirement for taking the Moodle exam, <a href="https://helpdesk.vatsim.uk">submit a ticket</a>
                        to the ATC Training Department in the helpdesk to get started.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-heathrow">
                    <div class="panel-heading">
                        <i class="fa fa-plane-departure" aria-hidden="true"></i> &thinsp; London Heathrow (S2+)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-heathrow" class="panel-collapse collapse panel-body">
                    View the details for the Heathrow endorsements by <a class="nav-link" href="{{ route('site.atc.heathrow') }}">clicking here</a>.

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-mil">
                    <div class="panel-heading">
                        <i class="fa fa-fighter-jet" aria-hidden="true"></i> &thinsp; Military (S2+)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-mil" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>

                    <p>
                        VATSIM UK offers controllers rated S2 or higher the opportunity to undertake a military endorsement,
                        through a number of self study courses. With this endorsement, controllers are permitted to open
                        military positions both in the UK and its overseas territories, in accordance with the Letters of
                        Agreement, Division Policy and military manual.
                    </p>

                    <h3>Endorsements Offered</h3>

                    <p>
                        We offer four types of military endorsement:
                    </p>

                    <ul>
                        <li>Tower and Ground</li>
                        <li>Approach</li>
                        <li>Precision Approach Radar (PAR)</li>
                        <li>Area Radar (CTR).</li>
                    </ul>

                    <h3>Airfields/Sectors Offered</h3>
                    <ul>
                        <li>All UK mainland military airfields</li>
                        <li>Gibraltar (LXGB)</li>
                        <li>RAF Akrotiri (LCRA)</li>
                        <li>RAF Mount Pleasant (EGYP)</li>
                        <li>RAF Ascension Island (FHAW)</li>
                        <li>EGVV_CTR - 133.900 - Swanwick Military - Covers military activity in both EGTT and EGPX, as well as top-down control for Military airfields</li>
                        <li>EGVV_x_CTR - Swanwick Military - Various other splits, as outlined in <a href="https://community.vatsim.uk/topic/37499-2020-12-03-swanwick-mil-sectorisation-and-frequency-change/">this procedure change post</a>.</li>
                        <li>ISLAND_CTR - Island Radar - Provides radar services to aircraft within the Falklands Control Zone, and top-down control for EGYP.</li>
                    </ul>

                    <h3>Requirements</h3>

                    <p>
                        In order to be able to be eligible for military endorsement, a controller must meet the following requirements.
                    </p>

                    <ul>
                        <li>Must be a home member or a visitor that has completed a visiting application</li>
                        <li>Must be rated S2 or higher</li>
                        <li>Must hold the equivalent permanent controller rating to the endorsement that they wish to undertake</li>
                        <li>Members wishing to undertake the PAR endorsement must hold the Approach endorsement.</li>
                    </ul>

                    <h3>Endorsement Process</h3>

                    <p>
                        The endorsement process for all military endorsements offered by VATSIM UK is as follows.
                    </p>

                    <ol>
                        <li><a href="https://helpdesk.vatsim.uk">Submit a ticket</a> to ATC Training in the helpdesk, requesting to have the exam enabled on the relevant self study course</li>
                        <li>Complete the online self-study course and pass the exam at the end</li>
                        <li>Upon completing the exam, submit a ticket to the helpdesk, stating that the course has been successfully completed</li>
                        <li>The endorsement will then be granted by the ATC Training Department.</li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-oceanic">
                    <div class="panel-heading">
                        <i class="fa fa-water" aria-hidden="true"></i> &thinsp; Shanwick Oceanic (C1+)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-oceanic" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>
                    <p>
                        Shanwick Oceanic (EGGX) is a designated special center under the terms of the <a
                        href="https://vatsim.net/docs/policy/global-ratings-policy">VATSIM Global Ratings Policy</a>.
                        Controllers rated C1 or higher may apply for an endorsement to open Shanwick (EGGX) and
                        Gander (CZQO) positions, or the combined Shanwick & Gander position (NAT_FSS).
                    </p>

                    <h3>Requirements</h3>

                    <p>
                        In order to be granted the Shanwick Oceanic endorsement, you must meet the following
                        requirements:
                    </p>

                    <ul>
                        <li>Home member or a visitor with an approved visiting application</li>
                        <li>Permanent controller of C1 or higher.</li>
                    </ul>

                    <h3>Endorsement Process</h3>

                    <p>
                        The endorsement process for the Shanwick Oceanic endorsement is as follows:
                    </p>

                    <ol>
                        <li>Enroll on the Shanwick Oceanic <a href="https://moodle.vatsim.uk/course/view.php?id=35">online Moodle course</a></li>
                        <li>Complete the Moodle course and pass the final exam</li>
                        <li>Practical training - there is no formal assessment, so mentors will issue the endorsement once competency is demonstrated.</li>
                    </ol>

                    <h3>Get Started</h3>

                    <p>
                        <strong>Home Members</strong> - please complete the online Moodle course and theory exam. Once
                        completed, please <a href="https://helpdesk.vatsim.uk">open a ticket</a> with ATC Training in
                        the helpdesk.
                    </p>

                    <p>
                        <strong>Visiting Members</strong> - please <a href="https://www.vatsim.uk/visit-transfer">complete
                        an application</a> to visit the UK. If you are already a visitor in the UK, please
                        <a href="https://helpdesk.vatsim.uk">open a ticket</a> with ATC Training in the helpdesk.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#endorsement-londonbbx">
                    <div class="panel-heading">
                        <i class="fa fa-map" aria-hidden="true"></i> &thinsp; London Bandbox (C1+)
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="endorsement-londonbbx" class="panel-collapse collapse panel-body">
                    <h3>Background</h3>
                    <p>
                        London Bandbox (LON_CTR) is a designated special center under the terms of the <a
                        href="https://vatsim.net/docs/policy/global-ratings-policy">VATSIM Global Ratings Policy</a>,
                        covering all four London sector groups (South, Central, North and West). Controllers rated C1
                        or higher may apply for an endorsement to enable them to open this position.
                    </p>

                    <h3>Requirements</h3>

                    <p>
                        In order to be granted a London Bandbox endorsement, the controller must meet the following
                        requirements:
                    </p>

                    <ul>
                        <li><strong>20 hours</strong> post-C1 on two of the four 'parent' London positions (40 total)</li>
                        <li><strong>10 hours</strong> post-C1 on the remaining two 'parent' London positions (20 total)</li>
                        <li>An additional <strong>20 hours</strong> post-C1 on any UK CTR position (including those above).</li>
                    </ul>

                    <p>
                        Time spent controlling split or bandbox positions (for example LTC_CTR or LON_SC_CTR) may only be
                        counted towards requirement 3 above.
                    </p>

                    <h3>Guidance</h3>

                    <p>
                        The amount of traffic in the London FIR varies significantly throughout the day and can increase
                        dramatically at very short notice. Furthermore, the amount of top-down controlling can vary
                        depending on which adjacent controllers are online. Controllers are therefore advised to use caution
                        when exercising the privileges of their validation. It is often wise to use the validation either
                        during the quieter hours of the evening, when there are a number of Approach positions online
                        covering the major aerodromes, or to cover off remaining sectors when other London positions are open.
                        Equally, if traffic levels increase significantly, controllers should consider downsizing to a
                        smaller sector.
                    </p>

                    <h3>Get Started</h3>

                    <p>
                        If you wish to apply for a London Bandbox endorsement, please <a href="https://helpdesk.vatsim.uk">open
                        a ticket</a> with ATC Training in the helpdesk. In the ticket, you should state your VATSIM CID,
                        the qualifying CTR positions you have controlled and time spent on each.
                    </p>

                </div>
            </div>
        </div>

    </div>

@stop
