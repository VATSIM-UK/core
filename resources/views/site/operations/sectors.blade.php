@extends('layout')

@section('content')

    <div class="row equal">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-book"></i> &thinsp; UK Area Sectors
                </div>
                <div class="panel-body">
                    <p>
                        The London (EGTT) and Scottish (EGPX) Flight Information Regions (FIRs) cover the enroute
                        airspace of the United Kingdom and coverage for underlying approach or aerodrome positions 
                        that are not online (&quot;top-down&quot;). 
                        
                        Our airspace is split into sectors that may seem daunting at first, but are easier to 
                        understand if you take a few minutes to digest the information below.
                    </p>
                    
                    <h3>
                        Radio Callsigns
                    </h3>

                    <p>
                        All area control sectors within the UK are called either &quot;London Control&quot; or 
                        &quot;Scottish Control&quot; over the radio. London Area (LON) and London Terminal (LTC) 
                        Control are referred to as &quot;London Control&quot;, regardless of which sector you are 
                        speaking to. Equally, Scottish Area (SCO) and Scottish Terminal (STC) Control are referred 
                        to as &quot;Scottish Control&quot; regardless of sector.
                    </p>

                    <p>
                        The Manchester TMA sectors (MAN) are splits of the London North sector, however are referred to
                        as &quot;Scottish Control&quot; when open. This is because they are controlled from the Area Control
                        Centre in Prestwick, Scotland, in real life. When only the parent sector, London North (LON_N), is
                        online, they are still referred to as &quot;London Control&quot; regardless of which part of the
                        sector you are in.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-top-down">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-plane"></i> &thinsp; Airfield Top-Down <i class="pull-right glyphicon glyphicon-menu-down"></i>
                    </div>
                </a>
                <div id="sectors-top-down" class="panel-collapse collapse panel-body">
                    <h3>What is top-down?</h3>
                    
                    <p>
                    In the absence of local aerodrome or approach ATC, our area sectors provide top-down service at airfields 
                    contained within the sector(s) they are controlling. You should request clearance, pushback, taxi etc. as 
                    you would if there were an aerodrome controller online, but should note that the extent of the top-down
                    service may be reduced by the controller if it is busy.
                    </p>
                    
                    <h3>Are all airfields covered top-down?</h3>
                    
                    <p>
                    At airfields outside of <a href="https://www.skybrary.aero/index.php/Controlled_Airspace#:~:text=SKYbrary%20Wiki,-If%20you%20wish&text=Controlled%20airspace%20is%20an%20airspace,accordance%20with%20the%20airspace%20classification.">
                    controlled airspace</a> you may, if you wish, depart at your own discretion. However, if you intend to join 
                    controlled airspace, it is generally advisable to request airways clearance from the controller prior to departure.
                    </p>
                    
                    <h3>How do I know who to call?</h3>
                    
                    <p>
                    If there are no aerodrome or approach services at your departure aerodrome, use the information below to 
                    determine which area sector to call. Contact the first controller listed (from left to right) that you can 
                    see online. Sometimes, the callsign mauy differ slightly, so it's best to cross-check with the frequency too.
                    </p>
                    
                    <p>
                    If in doubt, don't be afraid to ask. Another good place to check is the controller information, where 
                    information is usually displayed as to which areas the controller is covering.
                    </p>

                    <p>
                        <strong>EGCC / EGNJ / EGCN / EGNT / EGNV / EGNM</strong><br>
                        MAN_E, MAN, LON_N, LON
                    </p>

                    <p>
                        <strong>EGGP / EGNR / EGNS / EGNH</strong><br>
                        MAN_W, MAN, LON_N, LON
                    </p>

                    <p>
                        <strong>EGNX / EGBB / EGBE</strong><br>
                        LON_C, LON_SC, LON
                    </p>

                    <p>
                        <strong>EGSS / EGGW</strong><br>
                        LTC_NW, LTC_N, LTC, LON_C, LON_SC, LON
                    </p>

                    <p>
                        <strong>EGLL / EGLC / EGKB</strong><br>
                        LTC_SE, LTC_S, LTC, LON_D, LON_S, LON_SC, LON
                    </p>

                    <p>
                        <strong>EGKK</strong><br>
                        LTC_SW, LTC_S, LTC, LON_S, LON_SC, LON
                    </p>

                    <p>
                        <strong>EGHI / EGHH</strong><br>
                        LON_S, LON_SC, LON
                    </p>

                    <p>
                        <strong>EGGD / EGFF / EGTE / EGBJ / EGHQ / EGJJ / EGJB / EGJA</strong><br>
                        LON_W, LON
                    </p>

                    <p>
                        <strong>EGPH / EGPF / EGPK</strong><br>
                        STC, SCO_D, SCO_WD, SCO
                    </p>

                    <p>
                        <strong>EGAA / EGAC</strong><br>
                        STC_A, SCO_R, SCO_W, SCO_WD, SCO
                    </p>

                    <p>
                        <strong>EGPD / EGPE</strong><br>
                        SCO_E, SCO
                    </p>
                </div>
            </div>
        </div>

    </div>
    
    <div class="row equal">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-london">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-plane"></i> &thinsp; London (EGTT) FIR <i class="pull-right glyphicon glyphicon-menu-down"></i>
                    </div>
                </a>
                <div id="sectors-london" class="panel-collapse collapse panel-body">
                    
                    <p>
                    The London airspace is split into sectors that may seem daunting at first, but are easy to understand if you take a few minutes to digest the information below. 
                    The main London sectors (North, South, Central & West) are shown in the diagram below. These sectors are able to be split further during high traffic situations.
                    </p>

                    <img class="img-responsive center-block" src="/images/egttsectors-lon.png">
                    
                    <p>
                    The diagram below illustrates the London Terminal Control splits that you might see on a regular basis.
                    </p>

                    <img class="img-responsive center-block" src="/images/egttsectors-ltc.png">
                    
                    <h3>
                        Frequencies
                    </h3>

                    <h4>
                        London Standard Frequencies
                    </h4>

                    <p>
                        This table highlights the frequencies you may see on a typical night, without splitting from the
                        primary sectors.
                    </p>
                    <p class="table-responsive">
                    <table class="table table-borders">
                        <tbody class="text-center">
                        <tr>
                            <td rowspan="4" class="vertical-center" style="color: #ffffff; background: #777777;">
                                Bandbox<br>
                                LON_CTR<br>
                                (127.825)
                            </td>
                            <td rowspan="2" style="background: #6d4457; color: #ffffff;">
                                South Central<br>
                                LON_SC_CTR<br>
                                (132.600)
                            </td>
                            <td style="background: #000080; color: #ffffff;">
                                South LON_S_CTR (129.425)
                            </td>
                            <td rowspan="2" style="background: #6d4457; color: #ffffff;">
                                TC Bandbox<br>
                                LTC_CTR<br>
                                (135.800)
                            </td>
                        </tr>
                        <tr>
                            <td style="background: #f09626; color: #000000;">
                                Central LON_C_CTR (127.100)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="background: #800000; color: #ffffff;">
                                North LON_N_CTR (133.700)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="background: #008000; color: #ffffff;">
                                West LON_W_CTR (126.075)
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </p>

                    <h4>
                        London Sector Split Frequencies
                    </h4>

                    <p>
                        For the cases where the 4 main sectors are split (common for events), additional frequencies
                        (plus the coverage order) is displayed below.
                    </p>

                    <p class="table-responsive">
                    <table class="table table-borders">
                        <tbody class="text-center">
                        <tr>
                            <td rowspan="4" class="vertical-center" style="background: #000080; color: #ffffff;">
                                South<br>
                                LON_S<br>
                                (129.425)
                            </td>
                            <td rowspan="2" style="background: #000080; color: #ffffff;">
                                Worthing<br>
                                LON_S<br>
                                (129.425)
                            </td>
                            <td colspan="2" style="background: #000080; color: #ffffff;">
                                Worthing LON_S (129.425)
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="background: #5f5ff0; color: #ffffff;">
                                TC South<br>
                                LTC_S<br>
                                (134.125)
                            </td>
                            <td style="background: #cbe5ff; color: #000000;">
                                TC South West LTC_SW (133.175)
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="background: #5f5ff0; color: #ffffff;">
                                Dover<br>
                                LON_D<br>
                                (134.900)
                            </td>
                            <td style="background: #cbe5ff; color: #000000;">
                                TC South East LTC_SE (120.525)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #5f5ff0; color: #ffffff;">
                                Dover LON_D (134.900)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 0;">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="5" class="vertical-center" style="background: #f09626; color: #000000;">
                                Central<br>
                                LON_C<br>
                                (127.100)
                            </td>
                            <td rowspan="2" style="background: #f09626; color: #000000;">
                                Daventry<br>
                                LON_C<br>
                                (127.100)
                            </td>
                            <td colspan="2" style="background: #f09626; color: #000000;">
                                Daventry LON_C (127.100)
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="background: #fab464; color: #000000;">
                                TC North<br>
                                LTC_N<br>
                                (119.775)
                            </td>
                            <td style="background: #faf096; color: #000000;">
                                TC North West LTC_NW (121.275)
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="background: #fab464; color: #000000;">
                                <br>Clacton<br>
                                LON_E<br>
                                (118.475)
                            </td>
                            <td style="background: #faf096; color: #000000;">
                                TC North East LTC_NE (118.825)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #fab464; color: #000000;">
                                TC East LTC_E (121.225)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #fab464; color: #000000;">
                                Clacton LON_E (118.475)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 0;">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="3" class="vertical-center" style="background: #800000; color: #ffffff;">
                                North<br>
                                LON_N<br>
                                (133.700)
                            </td>
                            <td colspan="3" style="background: #800000; color: #ffffff;">
                                North LON_N (133.700)
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="background: #963232; color: #ffffff;">
                                Manchester<br>
                                MAN<br>
                                (118.775)
                            </td>
                            <td colspan="2" style="background: #bf7f7f; color: #000000;">
                                Manchester West MAN_W (128.050)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #bf7f7f; color: #000000;">
                                Manchester East MAN_E (133.800)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 0;">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="vertical-center" style="min-height: 100px; background: #008000; color: #ffffff;">
                                West<br>
                                LON_W<br>
                                (126.075)
                            </td>
                        </tr>
                        <tr>
                        </tr>
                        </tbody>
                    </table>
                    </p>
                    
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="row equal">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-scottish">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-plane"></i> &thinsp; Scottish (EGPX) FIR <i class="pull-right glyphicon glyphicon-menu-down"></i>
                    </div>
                </a>
                <div id="sectors-scottish" class="panel-collapse collapse panel-body">
                    
                    <p>
                    The diagram below illustrates the most basic of splits for Scottish (SCO_CTR) airspace. SCO_CTR covers both areas in the absence of a split controller.
                    Other splits are more common during events - the top-down table above reflects these splits - do ask a controller if you are unsure.
                    </p>

                    <img class="img-responsive center-block" src="/images/egpxsectors.png">
                    
                </div>
            </div>
        </div>

    </div>
    
    <div class="row equal">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-controller-info">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-plane"></i> &thinsp; Information for Controllers <i class="pull-right glyphicon glyphicon-menu-down"></i>
                    </div>
                </a>
                <div id="sectors-controller-info" class="panel-collapse collapse panel-body">
                    
                    <p>
                        For the purpose of <a href="{{ route('site.atc.bookings') }}">controller bookings</a>, 
                        the &#39;Primary Sectors&#39; are defined as:
                    </p>

                    <ul>
                        <li>
                            LON_S_CTR
                        </li>
                        <li>
                            LON_C_CTR
                        </li>
                        <li>
                            LON_W_CTR
                        </li>
                        <li>
                            LON_N_CTR
                        </li>
                        <li>
                            SCO_CTR
                        </li>
                    </ul>
                    
                    <p>
                        The &#39;Secondary Sectors&#39; are defined as:
                    <p>
                    
                    <ul>
                        <li>
                            LON_D_CTR
                        </li>
                        <li>
                            LON_E_CTR
                        </li>
                        <li>
                            LTC_S_CTR
                        </li>
                        <li>
                            LTC_N_CTR
                        </li>
                        <li>
                            MAN_CTR
                        </li>
                        <li>
                            SCO_CTR
                        </li>
                    </ul>
                    
                    <p>
                        Members may open either a single Primary or Secondary sector, or a valid combination of 
                        Primary (e.g. LON_SC_CTR) or Secondary (e.g. LTC_CTR) sectors.<br>
                    </p>
                    
                    <p>
                        Further splits require the remaining portion of the Primary or Secondary sector to be 
                        staffed too - e.g. opening LTC_NE_CTR requires LTC_NW_CTR (as the remaining portion of 
                        LTC_N_CTR) to be online.
                    </p>
                    
                </div>
            </div>
        </div>

    </div>

@stop
