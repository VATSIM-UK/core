@extends('layout')

@section('content')

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-map-marker" aria-hidden="true"></i> &thinsp; UK Area Sectors
                </div>
                <div class="panel-body">

                    <h3>
                        Introduction
                    </h3>

                    <p>
                        This page is to help pilots flying in UK airspace determine when they should contact one of our Area (CTR) sectors. We have tried to keep this as simple as possible, splitting the information into distinct sections depending on your type of flight.
                    </p>

                    <p>
                        If you are ever unsure, please PM one of our controllers saying that you have read the website but are unsure whether you need to contact them. They will gladly advise!
                    </p>

                    <p>
                        <strong>Do not call</strong> EGTT_I_CTR or EGPX_I_CTR as civilian ‘airliner’ traffic – these positions are for Flight Information Services outside of controlled airspace only.
                    </p>

                    <p>
                        <strong>Do not call</strong> EGVV_CTR or EGQQ_CTR as a civilian ‘airliner’ – these positions are primarily for Military operations. UK FIS may be available on request/as coordinated with civilian area controllers (normally only above FL100).
                    </p>

                    <h3>
                        Radio Callsigns
                    </h3>

                    <p>
                        The two main radio callsigns for Area controllers in the UK are:
                    </p>

                    <ol>
                        <li>&quot;London Control&quot; - all LON and LTC sectors</li>
                        <li>&quot;Scottish Control&quot; - all SCO, STC and MAN sectors</li>
                    </ol>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-departing-ifr">
                    <div class="panel-heading">
                        <i class="fa fa-plane-departure" aria-hidden="true"></i> &thinsp; I am <strong>departing IFR</strong> from a UK airfield...
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-departing-ifr" class="panel-collapse collapse panel-body">

                    <p>
                        Always contact <strong>local ATC</strong> (DEL/GND/TWR/APP) where it is online. There are a number of APP positions that cover more than one airfield top-down:
                    </p>

                    <ul>
                        <li>
                            <strong>EGAA_APP</strong> - Belfast/Aldergrove (EGAA) and Belfast/City (EGAC)
                        </li>
                        <li>
                            <strong>EGGP_APP</strong> - Liverpool (EGGP) and Hawarden (EGNR)
                        </li>
                        <li>
                            <strong>EGJJ_C_APP</strong> - Jersey (EGJJ), Guernsey (EGJB) and Alderney (EGJA)
                        </li>
                        <li>
                            <strong>EGNO_APP</strong> - Blackpool (EGNH) and Warton (EGNO)
                        </li>
                        <li>
                            <strong>EGNT_APP</strong> - Newcastle (EGNT) and Teesside (EGNV)
                        </li>
                        <li>
                            <strong>ESSEX_APP</strong> – London/Stansted (EGSS), London/Luton (EGGW) and Cambridge (EGSC)
                        </li>
                        <li>
                            <strong>EGSS_APP</strong> – London/Stansted (EGSS) and Cambridge (EGSC)
                        </li>
                        <li>
                            <strong>SOLENT_APP</strong> – Southampton (EGHI) and Bournemouth (EGHH)
                        </li>
                        <li>
                            <strong>THAMES_APP</strong> – London/City (EGLC), Southend (EGMC) and Biggin Hill (EGKB)
                        </li>
                    </ul>

                    <h3>
                        What is top-down?
                    </h3>

                    <p>
                        In the absence of local ATC, our area sectors provide a top-down service at airfields contained within the sector(s) they are controlling. You should request clearance, pushback, taxi etc. as you would if there were an aerodrome controller online.
                    </p>

                    <h3>Are all airfields covered top-down?</h3>

                    <p>
                        At airfields <strong>outside</strong> of <a href="https://www.skybrary.aero/index.php/Controlled_Airspace#:~:text=SKYbrary%20Wiki,-If%20you%20wish&text=Controlled%20airspace%20is%20an%20airspace,accordance%20with%20the%20airspace%20classification." target="_blank" rel="noopener noreferrer">
                        controlled airspace</a> you may, if you wish, depart at your own discretion. However, if you intend to join
                        controlled airspace, it is generally advisable to request joining clearance from the controller <strong>prior</strong> to departure.
                    </p>

                    <h3>
                        How do I know who to call?
                    </h3>

                    <p>
                        In the absence of local ATC, contact the first controller listed below that you see online (the priority is from left to right). Sometimes, the callsign may differ slightly, so it is best to cross-check the frequency too.
                    </p>

                    <p>
                        The location of these airports within our sectors are also marked on the diagrams in the next section.
                    </p>

                    <p style="margin-left: 40px">
                        <strong>Manchester (EGCC)</strong><br>
                        MAN_SE_CTR (134.425) &#8594; MAN_E_CTR (133.800) &#8594; MAN_CTR (133.200) &#8594; LON_N_CTR (133.700) &#8594; LON_NW_CTR (135.575) &#8594; LON_CTR (127.825)
                    </p>
					<p style="margin-left: 40px">
                        <strong>Leeds (EGNM), Doncaster (EGCN), Humberside (EGNJ), Newcastle (EGNT), Teesside (EGNV)</strong><br>
                        MAN_NE_CTR (135.700) &#8594; MAN_E_CTR (133.800) &#8594; MAN_CTR (133.200) &#8594; LON_NE_CTR (128.125) &#8594; LON_N_CTR (133.700) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Liverpool (EGGP), Hawarden (EGNR), Isle of Man (EGNS), Blackpool (EGNH)</strong><br>
                        MAN_W_CTR (128.050) &#8594; MAN_CTR (133.200) &#8594; LON_NW_CTR (135.575) &#8594; LON_N_CTR (133.700) &#8594; LON_CTR (127.825)
                    </p>
					<p style="margin-left: 40px">
                        <strong>Norwich (EGSH)</strong><br>
                        LON_NE_CTR (128.125) &#8594; LON_N_CTR (133.700) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>East Midlands (EGNX), Birmingham (EGBB), Coventry (EGBE)</strong><br>
                        LON_M_CTR (120.025) &#8594; LON_C_CTR (127.100) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>London/Stansted (EGSS), Cambridge (EGSC)</strong><br>
                        LTC_NE_CTR (118.825) &#8594; LTC_N_CTR (119.775) &#8594; LTC_CTR (135.800) &#8594; LTC_E_CTR (121.225) &#8594; LON_E_CTR (118.475) &#8594; LON_C_CTR (127.100) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>London/Luton (EGGW)</strong><br>
                        LTC_NW_CTR (121.275) &#8594; LTC_N_CTR (119.775) &#8594; LTC_CTR (135.800) &#8594; LON_M_CTR (120.025) &#8594; LON_C_CTR (127.100) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>London/Heathrow (EGLL), London/City (EGLC), Southend (EGMC), Biggin Hill (EGKB), Lydd (EGMD)</strong><br>
                        LTC_SE_CTR (120.525) &#8594; LTC_S_CTR (134.125) &#8594; LTC_CTR (135.800) &#8594; LON_D_CTR (134.900) &#8594; LON_S_CTR (129.425) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>London/Gatwick (EGKK), Farnborough (EGLF)</strong><br>
                        LTC_SW_CTR (133.175) &#8594; LTC_S_CTR (134.125) &#8594; LTC_CTR (135.800) &#8594; LON_S_CTR (129.425) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Southampton (EGHI), Bournemouth (EGHH)</strong><br>
                        LON_S_CTR (129.425) &#8594; LON_SC_CTR (132.600) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Bristol (EGGD), Cardiff (EGFF), Exeter (EGTE), Gloucester (EGBJ), Newquay (EGHQ), Channel Islands (EGJJ/JB/JA)</strong><br>
                        LON_W_CTR (126.075) &#8594; LON_CTR (127.825)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Edinburgh (EGPH), Glasgow (EGPF), Prestwick (EGPK)</strong><br>
                        STC_CTR (126.300) &#8594; SCO_D_CTR (135.850) &#8594; SCO_WD_CTR (133.875) &#8594; SCO_S_CTR (134.775) &#8594; SCO_CTR (135.525)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Belfast/Aldergrove (EGAA), Belfast/City (EGAC)</strong><br>
                        STC_A_CTR (123.775) &#8594; SCO_R_CTR (129.100) &#8594; SCO_W_CTR (132.725) &#8594; SCO_WD_CTR (133.875) &#8594; SCO_CTR (135.525)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Aberdeen/Dyce (EGPD), Dundee (EGPN)</strong><br>
                        SCO_S_CTR (134.775) &#8594; SCO_E_CTR (121.325) &#8594; SCO_CTR (135.525)
                    </p>
                    <p style="margin-left: 40px">
                        <strong>Inverness (EGPE), Stornoway (EGPO), Kirkwall (EGPA), Sumburgh (EGPB), Wick (EGPC)</strong><br>
                        SCO_N_CTR (129.225) &#8594; SCO_E_CTR (121.325) &#8594; SCO_CTR (135.525)
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-arriving-transiting">
                    <div class="panel-heading">
                        <i class="fa fa-plane-arrival" aria-hidden="true"></i> &thinsp; I am <strong>arriving</strong> at a UK airfield… / <i class="fa fa-plane" aria-hidden="true"></i> I am <strong>transiting</strong> through UK airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-arriving-transiting" class="panel-collapse collapse panel-body">

                    <p>
                        Our controllers will always send you a contact me if you are entering their airspace. However, if you’d like to pre-empt their call, please use the diagrams below for an idea of where each sector covers.
                    </p>

                    <p>
                        You are encouraged to look-up and plan for descent in accordance with your expected STAR. If you need to descend before you enter an online controller’s airspace, then please do.
                    </p>

                    <h3>
                        London Main Sectors
                    </h3>

                    <p>
                        The main London sectors (North, South, Central & West) are shown below. Sometimes these sectors are bandboxed using the LON_SC_CTR or LON_CTR callsigns.
                    </p>

                    <img class="img-responsive center-block" src="/images/egttsectors-lon.png" alt="diagram of main London sectors">

                    <p>
                        e.g. If LON_CTR, LON_S_CTR and LON_W_CTR are online, then the...
                    </p>

                    <ul>
                        <li>
                            <span style="color:#990000; font-weight:bold">Red (North)</span> sector will be controlled by LON_CTR;
                        </li>
                        <li>
                            <span style="color:#e69138; font-weight:bold">Yellow (Central)</span> sector by LON_CTR;
                        </li>
                        <li>
                            <span style="color:#0b5394; font-weight:bold">Blue (South)</span> sector by LON_S_CTR; and
                        </li>
                        <li>
                            <span style="color:#38761d; font-weight:bold">Green (West)</span> sector by LON_W_CTR.
                        </li>
                    </ul>

                    <h3>
                        London TMA Split Sectors
                    </h3>

                    <p>
                        We regularly split off the airspace in the London TMA area with LTC sectors, which are shown in the diagram below. There are 4 main sectors (NE, NW, SE, SW) which can be ‘bandboxed’ together using the LTC_N_CTR, LTC_S_CTR or LTC_CTR callsigns.
                    </p>

                    <p>
                        The top of these sectors is generally at FL155, up to a maximum of FL185.
                    </p>

                    <p>
                        It is especially important to descend in accordance with the STAR when only these sectors are online.
                    </p>

                    <img class="img-responsive center-block" src="/images/egttsectors-ltc-2202.png" alt="diagram of London TMA split sectors">

                    <h3>
                        Scottish Main Sectors
                    </h3>

                    <p>
                        The most frequent callsign you will see for Scottish Control is SCO_CTR which bandboxes all Scottish airspace. Two main splits are shown on the diagram below.
                    </p>

                    <p>
                        The STC_CTR callsign covers an area <strong>below FL255</strong> around the Scottish TMA (near the main airports, EGPH, EGPF and EGPK).
                    </p>

                    <p>
                        The STC_A_CTR callsign covers an area <strong>below FL255</strong> in the Belfast area, with responsibility for EGAA and EGAC traffic.
                    </p>

                    <p>
                        If Scottish airspace is split any further, it will most likely be during events when lots of ATC is online to direct you between controllers, so do not worry.
                    </p>

                    <img class="img-responsive center-block" src="/images/egpxsectors.png" alt="diagram of main Scottish sectors">

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-vfr">
                    <div class="panel-heading">
                        <i class="fa fa-binoculars" aria-hidden="true"></i> &thinsp; I am flying <strong>VFR</strong> within UK airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-vfr" class="panel-collapse collapse panel-body">

                    <p>
                        If you are departing from an airfield <strong>inside</strong> controlled airspace, then you <strong>must</strong> obtain clearance for all stages of your departure from the relevant area controller, as set out in Section 1.
                    </p>

                    <p>
                        If you are departing from an airfield <strong>outside</strong> of controlled airspace, then you <strong>may</strong> ask the area controller directly above that airfield whether they are able to provide an air traffic service.
                    </p>

                    <p>
                        If you have departed from an airfield without speaking to ATC but wish to enter controlled airspace, you must obtain a clearance from the area controller that covers that airspace before doing so. Feel free to ask a controller whether they cover airspace if you are unsure.
                    </p>

                    <h3>London &amp; Scottish Information</h3>

                    <p style="margin-left: 40px">
                        <strong>EGTT_I_CTR</strong> (124.600) – &quot;London Information&quot;
                    </p>

                    <p style="margin-left: 40px">
                        <strong>EGPX_I_CTR</strong> (119.875) – &quot;Scottish Information&quot;
                    </p>

                    <p>
                        Both of these positions are able to provide a <strong>Basic Service</strong>, which is a type of UK Flight Information Service (UK FIS) (see page 71 of <a href="https://publicapps.caa.co.uk/modalapplication.aspx?appid=11&mode=detail&id=7919" target="_blank" rel="noopener noreferrer">The Skyway Code</a>), to aircraft operating outside of controlled airspace.
                    </p>

                    <p>
                        They are also able to coordinate airways joining clearances for IFR departures from airfields outside of controlled airspace.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-military">
                    <div class="panel-heading">
                        <i class="fa fa-fighter-jet" aria-hidden="true"></i> &thinsp; I am operating a <strong>military</strong> flight within UK mainland airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-military" class="panel-collapse collapse panel-body">

                    <p>
                        Military airfields are <strong>not</strong> covered &apos;top-down&apos; by the civilian Area Control positions outlined in Section 1.
                    </p>

                    <p>
                        The same principles apply for military operations – contact local ATC if online but otherwise, the two main relevant callsigns are:
                    </p>

                    <p style="margin-left: 40px">
                        <strong>EGVV_CTR</strong> (135.150) – &quot;Swanwick Mil&quot; <br>
                        Covers <strong>EGTT FIR</strong> airspace and airports for military operations, as well as in the <strong>EGPX FIR</strong> when Swanwick Mil (North sector) is offline.
                    </p>

                    <p style="margin-left: 40px">
                        <strong>EGQQ_CTR</strong> (134.300) - &quot;Swanwick Mil&quot; (North sector) <br>
                        Covers <strong>EGPX FIR</strong> airspace and airports for military operations.
                    </p>

                    <p>
                        If you are departing from a military airfield and joining the airways system as civilian air traffic does, please ensure you request airways joining clearance from the relevant civilian area sector (in Section 2) <strong>before</strong> you enter controlled airspace. If you are unsure who to obtain this from, please ask a controller.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-controller-info">
                    <div class="panel-heading">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> &thinsp; Controller Information
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-controller-info" class="panel-collapse collapse panel-body">

                    <p>
                        For the purpose of <a href="{{ route('site.atc.bookings') }}">controller bookings</a>,
                        the &#39;Primary Sectors&#39; are defined as:
                    </p>

                    <ul>
                        <li>
                            SCO_CTR
                        </li>                    
                        <li>
                            LON_N_CTR
                        </li>
                        <li>
                            LON_C_CTR
                        </li>                        
                        <li>
                            LON_S_CTR
                        </li>
                        <li>
                            LON_W_CTR
                        </li>
                    </ul>

                    <p>
                        The &#39;Secondary Sectors&#39; are defined as:
                    <p>

                    <ul>
                        <li>
                            LON_NW_CTR
                        </li>
                        <li>
                            LON_NE_CTR
                        </li>
                        <li>
                            LON_M_CTR
                        </li>
                        <li>
                            LON_E_CTR
                        </li>                        
                        <li>
                            LON_D_CTR
                        </li>
                        <li>
                            SCO_WD_CTR
                        </li>
                        <li>
                            STC_CTR
                        </li>
                        <li>
                            STC_A_CTR
                        </li>
                        <li>
                            MAN_W_CTR
                        </li>
                        <li>
                            MAN_NE_CTR
                        </li>
                        <li>
                            MAN_SE_CTR
                        </li>                          
                        <li>
                            LTC_N_CTR
                        </li>
                        <li>
                            LTC_S_CTR
                        </li>                      
                    </ul>

                    <p>
                        Members may open either a single Primary or Secondary sector, or a valid combination of
                        Primary (e.g. LON_SC_CTR) or Secondary (e.g. LTC_CTR, MAN_CTR) sectors.<br>
                    </p>

                    <p>
                        Further splits require the remaining portion of the Primary or Secondary sector to be
                        staffed too - e.g. opening LTC_NE_CTR requires LTC_NW_CTR (as the remaining portion of
                        LTC_N_CTR) to be online. Splits not defined in the London or Scottish FIR (EGTT) vMATS 
                        Part II require specific approval from the Operations Department in the form of a 
                        Temporary Instruction or Procedure Change.
                    </p>

                </div>
            </div>
        </div>

    </div>

@stop
